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

namespace Maghos\Eet\Model;

use DateTime;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Maghos\Eet\Api\Data\RecordInterface;
use Maghos\Eet\Api\RecordRepositoryInterface;
use Maghos\Eet\Api\RecordSenderInterface;
use Maghos\Eet\Helper\Data as EetHelper;
use Maghos\Eet\Model\Api\CertificateFactory;
use Maghos\Eet\Model\Api\Client;
use Maghos\Eet\Model\Api\Exception\CertificateException;
use Maghos\Eet\Model\Api\Exception\ConnectionException;
use Maghos\Eet\Model\Api\Response;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RecordSender implements RecordSenderInterface
{
    private const DATE_FORMAT = 'Y-m-d H:i:s';
    private const CURRENCY_CZK = 'CZK';

    /** @var OrderRepositoryInterface */
    private OrderRepositoryInterface $orderRepository;

    /** @var EetHelper */
    private EetHelper $eetHelper;

    /** @var RecordRepositoryInterface */
    private RecordRepositoryInterface $recordRepository;

    /** @var Client */
    private Client $client;

    /** @var CertificateFactory */
    private CertificateFactory $certificateFactory;

    /** @var CurrencyFactory */
    private CurrencyFactory $currencyFactory;

    /** @var LoggerInterface */
    private LoggerInterface $logger;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param EetHelper $eetHelper
     * @param RecordRepositoryInterface $recordRepository
     * @param Client $client
     * @param CertificateFactory $certificateFactory
     * @param CurrencyFactory $currencyFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        EetHelper $eetHelper,
        RecordRepositoryInterface $recordRepository,
        Client $client,
        CertificateFactory $certificateFactory,
        CurrencyFactory $currencyFactory,
        LoggerInterface $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->eetHelper = $eetHelper;
        $this->recordRepository = $recordRepository;
        $this->client = $client;
        $this->certificateFactory = $certificateFactory;
        $this->currencyFactory = $currencyFactory;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function sendInvoice(InvoiceInterface $invoice): bool
    {
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $order = $invoice->getOrder();
        $amount = $this->getAmountCzk($invoice, $order);
        $record = $this->createRecord($order, (string)$invoice->getIncrementId(), $amount)
            ->setInvoiceId((int)$invoice->getEntityId());

        return $this->send($record, $order, true);
    }

    /**
     * @inheritDoc
     */
    public function sendCreditmemo(CreditmemoInterface $creditmemo): bool
    {
        /** @var \Magento\Sales\Model\Order\Creditmemo $creditmemo */
        $order = $creditmemo->getOrder();
        $amount = -$this->getAmountCzk($creditmemo, $order);
        $record = $this->createRecord($order, (string)$creditmemo->getIncrementId(), $amount)
            ->setCreditmemoId((int)$creditmemo->getEntityId())
            ->setIsRefund(true);

        return $this->send($record, $order, true);
    }

    /**
     * @inheritDoc
     */
    public function resendRecord(RecordInterface $record): bool
    {
        return $this->send($record, $this->orderRepository->get((int)$record->getOrderId()), false);
    }

    /**
     * @inheritDoc
     */
    public function eetConfigTest(?string $scopeCode = null, bool $websiteScope = false): array
    {
        $isTest = $this->eetHelper->isTestEnv($scopeCode, $websiteScope);
        $record = $this->recordRepository->create()
            ->setEic($this->eetHelper->getEic($scopeCode, $websiteScope))
            ->setUnitId($this->eetHelper->getUnitId($scopeCode, $websiteScope))
            ->setRegisterId($this->eetHelper->getCheckoutId($scopeCode, $websiteScope))
            ->setReceiptNumber('TEST')
            ->setAmount(1.0)
            ->setCreatedAt((new DateTime())->format(self::DATE_FORMAT));

        $messages = [];
        try {
            $certificate = $this->certificateFactory->create([
                'data' => $this->eetHelper->getCertContents($scopeCode, $websiteScope),
                'password' => $this->eetHelper->getCertPassword($scopeCode, $websiteScope),
            ]);
            $response = $this->client->send($record, $certificate, $isTest, true, true);
            $messages[] = $response->isSuccess()
                ? __('EET test success!')
                : __('EET test failure: Server refused the receipt: %1.', $this->describe($response));
            foreach ($response->getWarnings() as $warning) {
                $messages[] = __('EET warning: %1', $warning);
            }
        } catch (CertificateException $e) {
            $messages[] = __('EET test failure: missing/bad certificate.');
        } catch (ConnectionException $e) {
            $messages[] = __('EET test failure: EET server seem to be down, try again later.') . ' ' . $e->getMessage();
        } catch (\Throwable $e) {
            $messages[] = __('EET test failure: Unknown exception: %1.', $e->getMessage());
        }

        $messages[] = $isTest
            ? __('Test environment (playground) is enabled. Test will work only with a playground certificate.')
            : __('Production environment is used in verification mode, no sale is registered by this test.');

        return $messages;
    }

    /**
     * Create a new sale record from the order and store configuration.
     *
     * @param \Magento\Sales\Model\Order $order
     * @param string $receiptNumber
     * @param float $amount
     * @return RecordInterface
     */
    private function createRecord($order, string $receiptNumber, float $amount): RecordInterface
    {
        $storeId = $order->getStoreId();

        return $this->recordRepository->create()
            ->setOrderId((int)$order->getEntityId())
            ->setEic($this->eetHelper->getEic($storeId))
            ->setUnitId($this->eetHelper->getUnitId($storeId))
            ->setRegisterId($this->eetHelper->getCheckoutId($storeId))
            ->setReceiptNumber($receiptNumber)
            ->setAmount(round($amount, 2))
            ->setStatus(RecordInterface::STATUS_NEW)
            ->setCreatedAt((new DateTime())->format(self::DATE_FORMAT));
    }

    /**
     * Send the record and store the result.
     *
     * @param RecordInterface $record
     * @param OrderInterface $order
     * @param bool $firstSend
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    private function send(RecordInterface $record, OrderInterface $order, bool $firstSend): bool
    {
        $storeId = $order->getStoreId();
        $status = RecordInterface::STATUS_ERROR;
        try {
            $certificate = $this->certificateFactory->create([
                'data' => $this->eetHelper->getCertContents($storeId),
                'password' => $this->eetHelper->getCertPassword($storeId),
            ]);
            $response = $this->client->send($record, $certificate, $this->eetHelper->isTestEnv($storeId), $firstSend);
            $status = $this->getStatus($response);
            $record->setPok($response->getPok());
            if ($status !== RecordInterface::STATUS_SENT || $response->getWarnings()) {
                $this->logger->warning(
                    'EET: ' . $this->describe($response),
                    ['receipt' => $record->getReceiptNumber()]
                );
            }
        } catch (\Throwable $e) {
            $this->logger->error(
                'EET: sending failed: ' . $e->getMessage(),
                ['exception' => $e, 'receipt' => $record->getReceiptNumber(), 'first_send' => $firstSend]
            );
        }

        $this->recordRepository->save($record->setStatus($status));

        return $status === RecordInterface::STATUS_SENT;
    }

    /**
     * Map the server response to the record status; only temporary errors are worth resending.
     *
     * @param Response $response
     * @return int
     */
    private function getStatus(Response $response): int
    {
        if ($response->getPok() !== null) {
            return RecordInterface::STATUS_SENT;
        }

        return $response->getErrorCode() === Response::ERROR_TEMPORARY
            ? RecordInterface::STATUS_ERROR
            : RecordInterface::STATUS_REJECTED;
    }

    /**
     * Describe the response for logs and admin messages.
     *
     * @param Response $response
     * @return string
     */
    private function describe(Response $response): string
    {
        return trim(sprintf(
            '%s %s %s (transaction %s)',
            $response->getErrorCode() !== null ? '[' . $response->getErrorCode() . ']' : '',
            $response->getMessage(),
            implode('; ', $response->getWarnings()),
            $response->getTransactionId()
        ));
    }

    /**
     * Get the grand total of the invoice or credit memo in CZK.
     *
     * @param \Magento\Framework\Model\AbstractModel $entity
     * @param \Magento\Sales\Model\Order $order
     * @return float
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAmountCzk($entity, $order): float
    {
        if ($order->getOrderCurrencyCode() === self::CURRENCY_CZK) {
            return (float)$entity->getData('grand_total');
        }

        $baseAmount = (float)$entity->getData('base_grand_total');
        if ($order->getBaseCurrencyCode() === self::CURRENCY_CZK) {
            return $baseAmount;
        }

        $currency = $this->currencyFactory->create()->load($order->getBaseCurrencyCode());
        return (float)$currency->convert($baseAmount, self::CURRENCY_CZK);
    }
}
