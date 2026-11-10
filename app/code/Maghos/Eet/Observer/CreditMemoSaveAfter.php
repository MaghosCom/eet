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

class CreditMemoSaveAfter implements ObserverInterface
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
     * Send the refund EET receipt for a credit memo of an order whose sale was registered.
     *
     * @param Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(Observer $observer): void
    {
        /** @var \Magento\Sales\Model\Order\Creditmemo $creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $order = $creditmemo->getOrder();
        if (!$this->eetHelper->isEnabled($order->getStoreId())) {
            return;
        }

        // the credit memo is saved again later (comments, e-mail flag), send its refund only once
        if ($this->recordRepository->getRecordBy(RecordInterface::ORDER_ID, (int)$order->getEntityId())
            && !$this->recordRepository->getRecordBy(
                RecordInterface::CREDITMEMO_ID,
                (int)$creditmemo->getEntityId(),
                true
            )
        ) {
            $this->recordSender->sendCreditmemo($creditmemo);
        }
    }
}
