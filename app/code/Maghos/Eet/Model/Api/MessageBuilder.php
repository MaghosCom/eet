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

use DateTime;
use DateTimeZone;
use DOMDocument;
use DOMElement;
use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Maghos\Eet\Api\Data\RecordInterface;

/**
 * Builds the EET 2.0 (interface v4) SOAP message signed according to WS-Security.
 */
class MessageBuilder
{
    public const NS_EET = 'http://fs.gov.cz/eet/schema/v4';
    public const NS_SOAP = 'http://schemas.xmlsoap.org/soap/envelope/';
    private const NS_WSSE = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
    private const NS_WSU = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd';
    private const NS_DS = 'http://www.w3.org/2000/09/xmldsig#';
    private const NS_EC = 'http://www.w3.org/2001/10/xml-exc-c14n#';
    private const TOKEN_PREFIX = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-';
    private const ENCODING_BASE64 = self::TOKEN_PREFIX . 'soap-message-security-1.0#Base64Binary';
    private const TYPE_X509 = self::TOKEN_PREFIX . 'x509-token-profile-1.0#X509v3';
    private const ALGO_C14N = 'http://www.w3.org/2001/10/xml-exc-c14n#';
    private const ALGO_RSA_SHA256 = 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256';
    private const ALGO_SHA256 = 'http://www.w3.org/2001/04/xmlenc#sha256';
    private const TIMEZONE = 'Europe/Prague';
    private const DATE_FORMAT = 'Y-m-d\TH:i:sP';

    /** @var IdentityGeneratorInterface */
    private IdentityGeneratorInterface $identityGenerator;

    /**
     * @param IdentityGeneratorInterface $identityGenerator
     */
    public function __construct(IdentityGeneratorInterface $identityGenerator)
    {
        $this->identityGenerator = $identityGenerator;
    }

    /**
     * Build the signed SOAP message for the record.
     *
     * @param RecordInterface $record
     * @param Certificate $certificate
     * @param bool $firstSend
     * @param bool $verification
     * @return string
     * @throws Exception\CertificateException
     * @throws \Exception
     */
    public function build(
        RecordInterface $record,
        Certificate $certificate,
        bool $firstSend = true,
        bool $verification = false
    ): string {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $envelope = $doc->appendChild($doc->createElementNS(self::NS_SOAP, 'soapenv:Envelope'));
        $envelope->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:v4', self::NS_EET);
        $header = $envelope->appendChild($doc->createElementNS(self::NS_SOAP, 'soapenv:Header'));
        $body = $envelope->appendChild($doc->createElementNS(self::NS_SOAP, 'soapenv:Body'));

        $sale = $body->appendChild($doc->createElementNS(self::NS_EET, 'v4:Trzba'));
        $this->appendElement($sale, self::NS_EET, 'v4:Hlavicka', array_filter([
            'uuid_zpravy' => $this->identityGenerator->generateId(),
            'dat_odesl' => $this->formatDate(new DateTime()),
            'prvni_zaslani' => $firstSend ? 'true' : 'false',
            'overeni' => $verification ? 'true' : null,
        ]));
        $this->appendElement($sale, self::NS_EET, 'v4:Data', [
            'eic_popl' => $record->getEic(),
            'id_jednotky' => $record->getUnitId(),
            'id_pokl' => $record->getRegisterId(),
            'porad_cis' => $record->getReceiptNumber(),
            'dat_trzby' => $this->formatDate(new DateTime((string)$record->getCreatedAt(), new DateTimeZone('UTC'))),
            'celk_trzba' => $this->formatAmount($record->getAmount()),
        ]);

        $this->sign($header, $body, $certificate);

        return (string)$doc->saveXML();
    }

    /**
     * Add the WS-Security header with the certificate and the signature of the SOAP body.
     *
     * @param DOMElement $header
     * @param DOMElement $body
     * @param Certificate $certificate
     * @return void
     * @throws Exception\CertificateException
     */
    private function sign(DOMElement $header, DOMElement $body, Certificate $certificate): void
    {
        $suffix = bin2hex(random_bytes(8));
        $bodyId = 'id-' . $suffix;
        $tokenId = 'X509-' . $suffix;

        $body->setAttributeNS(self::NS_WSU, 'wsu:Id', $bodyId);
        $security = $this->appendElement($header, self::NS_WSSE, 'wsse:Security');
        $token = $this->appendElement($security, self::NS_WSSE, 'wsse:BinarySecurityToken', [
            'EncodingType' => self::ENCODING_BASE64,
            'ValueType' => self::TYPE_X509,
        ]);
        $token->setAttributeNS(self::NS_WSU, 'wsu:Id', $tokenId);
        $token->appendChild($header->ownerDocument->createTextNode($certificate->getCertificateBase64()));

        $signature = $this->appendElement($security, self::NS_DS, 'ds:Signature');
        $signedInfo = $this->appendElement($signature, self::NS_DS, 'ds:SignedInfo');
        $c14n = $this->appendElement($signedInfo, self::NS_DS, 'ds:CanonicalizationMethod', [
            'Algorithm' => self::ALGO_C14N,
        ]);
        $this->appendElement($c14n, self::NS_EC, 'ec:InclusiveNamespaces', ['PrefixList' => 'soapenv v4']);
        $this->appendElement($signedInfo, self::NS_DS, 'ds:SignatureMethod', ['Algorithm' => self::ALGO_RSA_SHA256]);

        $reference = $this->appendElement($signedInfo, self::NS_DS, 'ds:Reference', ['URI' => '#' . $bodyId]);
        $transforms = $this->appendElement($reference, self::NS_DS, 'ds:Transforms');
        $transform = $this->appendElement($transforms, self::NS_DS, 'ds:Transform', ['Algorithm' => self::ALGO_C14N]);
        $this->appendElement($transform, self::NS_EC, 'ec:InclusiveNamespaces', ['PrefixList' => 'v4']);
        $this->appendElement($reference, self::NS_DS, 'ds:DigestMethod', ['Algorithm' => self::ALGO_SHA256]);
        $digest = base64_encode(hash('sha256', (string)$body->C14N(true, false, null, ['v4']), true));
        $this->appendElement($reference, self::NS_DS, 'ds:DigestValue')->nodeValue = $digest;

        $signatureValue = $certificate->sign((string)$signedInfo->C14N(true, false, null, ['soapenv', 'v4']));
        $this->appendElement($signature, self::NS_DS, 'ds:SignatureValue')->nodeValue = base64_encode($signatureValue);

        $keyInfo = $this->appendElement($signature, self::NS_DS, 'ds:KeyInfo');
        $tokenReference = $this->appendElement($keyInfo, self::NS_WSSE, 'wsse:SecurityTokenReference');
        $this->appendElement($tokenReference, self::NS_WSSE, 'wsse:Reference', [
            'URI' => '#' . $tokenId,
            'ValueType' => self::TYPE_X509,
        ]);
    }

    /**
     * Append a namespaced element with attributes.
     *
     * @param DOMElement $parent
     * @param string $namespace
     * @param string $name
     * @param array $attributes
     * @return DOMElement
     */
    private function appendElement(
        DOMElement $parent,
        string $namespace,
        string $name,
        array $attributes = []
    ): DOMElement {
        $element = $parent->ownerDocument->createElementNS($namespace, $name);
        foreach ($attributes as $attribute => $value) {
            $element->setAttribute($attribute, (string)$value);
        }
        $parent->appendChild($element);

        return $element;
    }

    /**
     * Format date in Czech local time with the zone offset.
     *
     * @param DateTime $date
     * @return string
     */
    private function formatDate(DateTime $date): string
    {
        return $date->setTimezone(new DateTimeZone(self::TIMEZONE))->format(self::DATE_FORMAT);
    }

    /**
     * Format amount with exactly two decimals, without "-0.00".
     *
     * @param float $amount
     * @return string
     */
    private function formatAmount(float $amount): string
    {
        return number_format(abs($amount) < 0.005 ? 0.0 : $amount, 2, '.', '');
    }
}
