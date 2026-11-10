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

namespace Maghos\Eet\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Maghos\Eet\Api\Data\RecordInterface;
use Maghos\Eet\Api\RecordRepositoryInterface;
use Maghos\Eet\Api\RecordSenderInterface;
use Maghos\Eet\Helper\Data as EetHelper;

class InvoicePlaceAfter implements ObserverInterface
{
    /** @var EetHelper */
    private EetHelper $eetHelper;

    /** @var RecordRepositoryInterface */
    private RecordRepositoryInterface $recordRepository;

    /** @var RecordSenderInterface */
    private RecordSenderInterface $recordSender;

    /**
     * @param EetHelper $eetHelper
     * @param RecordRepositoryInterface $recordRepository
     * @param RecordSenderInterface $recordSender
     */
    public function __construct(
        EetHelper $eetHelper,
        RecordRepositoryInterface $recordRepository,
        RecordSenderInterface $recordSender
    ) {
        $this->eetHelper = $eetHelper;
        $this->recordRepository = $recordRepository;
        $this->recordSender = $recordSender;
    }

    /**
     * Send the EET receipt of an invoice of an order paid by an EET payment method.
     *
     * @param Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(Observer $observer): void
    {
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $observer->getEvent()->getInvoice();
        $order = $invoice->getOrder();
        $storeId = $order->getStoreId();
        $method = $order->getPayment() ? $order->getPayment()->getMethod() : null;

        if (!$this->eetHelper->isEnabled($storeId)
            || !in_array($method, $this->eetHelper->getPaymentMethods($storeId), true)
        ) {
            return;
        }

        // an existing record is either sent already or will be resent by cron
        if (!$this->recordRepository->getRecordBy(RecordInterface::INVOICE_ID, (int)$invoice->getEntityId())) {
            $this->recordSender->sendInvoice($invoice);
        }
    }
}
