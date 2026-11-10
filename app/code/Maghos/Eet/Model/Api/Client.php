<?php
/**
 *
 * Maghos_Eet Magento 2 extension
 *
 * NOTICE OF LICENSE
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 *
 * @category Maghos
 * @package Maghos_Eet
 * @copyright Copyright (c) 2026 Maghos
 * @license http://www.maghos.com/business-license
 * @author Magento dev team <support@maghos.com>
 */
declare(strict_types=1);

namespace Maghos\Eet\Model\Api;

use DOMDocument;
use DOMXPath;
use Magento\Framework\HTTP\Client\CurlFactory;
use Maghos\Eet\Api\Data\RecordInterface;
use Maghos\Eet\Model\Api\Exception\ConnectionException;

/**
 * Sends registered sale data messages to the EET 2.0 web service.
 */
class Client
{
    public const URL_PRODUCTION = 'https://trzbyeet.gov.cz/eet/services/EETServiceSOAP/v4';
    public const URL_PLAYGROUND = 'https://pg.trzbyeet.gov.cz/eet/services/EETServiceSOAP/v4';
    private const SOAP_ACTION = 'http://fs.gov.cz/eet/OdeslaniTrzby';
    private const CONNECT_TIMEOUT = 5;
    private const TIMEOUT = 10;

    /** @var MessageBuilder */
    private MessageBuilder $messageBuilder;

    /** @var CurlFactory */
    private CurlFactory $curlFactory;

    /** @var ResponseFactory */
    private ResponseFactory $responseFactory;

    /**
     * @param MessageBuilder $messageBuilder
     * @param CurlFactory $curlFactory
     * @param ResponseFactory $responseFactory
     */
    public function __construct(
        MessageBuilder $messageBuilder,
        CurlFactory $curlFactory,
        ResponseFactory $responseFactory
    ) {
        $this->messageBuilder = $messageBuilder;
        $this->curlFactory = $curlFactory;
        $this->responseFactory = $responseFactory;
    }

    /**
     * Send the record to EET and return the server answer.
     *
     * @param RecordInterface $record
     * @param Certificate $certificate
     * @param bool $playground
     * @param bool $firstSend
     * @param bool $verification
     * @return Response
     * @throws ConnectionException
     * @throws Exception\CertificateException
     * @throws \Exception
     */
    public function send(
        RecordInterface $record,
        Certificate $certificate,
        bool $playground,
        bool $firstSend = true,
        bool $verification = false
    ): Response {
        $message = $this->messageBuilder->build($record, $certificate, $firstSend, $verification);

        $curl = $this->curlFactory->create();
        $curl->setTimeout(self::TIMEOUT);
        $curl->setOption(CURLOPT_CONNECTTIMEOUT, self::CONNECT_TIMEOUT);
        $curl->setHeaders([
            'Content-Type' => 'text/xml; charset=utf-8',
            'SOAPAction' => '"' . self::SOAP_ACTION . '"',
        ]);
        try {
            $curl->post($playground ? self::URL_PLAYGROUND : self::URL_PRODUCTION, $message);
        } catch (\Exception $e) {
            throw new ConnectionException(__('EET server connection failed: %1', $e->getMessage()), $e);
        }

        $headers = array_change_key_case($curl->getHeaders());
        return $this->parse($curl->getBody(), (string)($headers['x-global-transaction-id'] ?? ''), $curl->getStatus());
    }

    /**
     * Parse the SOAP answer.
     *
     * @param string $body
     * @param string $transactionId
     * @param int $status
     * @return Response
     * @throws ConnectionException
     */
    private function parse(string $body, string $transactionId, int $status): Response
    {
        $doc = new DOMDocument();
        if ($body === '' || !$doc->loadXML($body, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING)) {
            throw new ConnectionException(
                __('EET server returned an invalid response (HTTP %1, transaction %2).', $status, $transactionId)
            );
        }

        // shortcut: the acknowledgement signature is not verified, TLS authenticates the server; verify it
        // against the I.CA certificate if an audit requires it
        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('eet', MessageBuilder::NS_EET);
        $xpath->registerNamespace('soap', MessageBuilder::NS_SOAP);

        $warnings = [];
        /** @var \DOMElement $warning */
        foreach ($xpath->query('//eet:Odpoved/eet:Varovani') as $warning) {
            $warnings[] = $warning->getAttribute('kod_varov') . ': ' . trim($warning->textContent);
        }

        /** @var \DOMElement|null $confirmation */
        $confirmation = $xpath->query('//eet:Odpoved/eet:Potvrzeni')->item(0);
        /** @var \DOMElement|null $error */
        $error = $xpath->query('//eet:Odpoved/eet:Chyba')->item(0);
        if ($confirmation === null && $error === null) {
            $fault = trim((string)$xpath->evaluate('string(//soap:Fault/faultstring)'));
            throw new ConnectionException(
                __('EET server returned no answer (HTTP %1, transaction %2): %3', $status, $transactionId, $fault)
            );
        }

        $element = $confirmation ?? $error;
        return $this->responseFactory->create([
            'pok' => $confirmation ? $confirmation->getAttribute('pok') : null,
            'errorCode' => $error ? (int)$error->getAttribute('kod') : null,
            'message' => $error ? trim($error->textContent) : '',
            'warnings' => $warnings,
            'test' => in_array($element->getAttribute('test'), ['true', '1'], true),
            'transactionId' => $transactionId,
        ]);
    }
}
