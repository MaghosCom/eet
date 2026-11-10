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

namespace Maghos\Eet\Plugin\Payment\Block;

use Magento\Payment\Block\Info as InfoBlock;
use Maghos\Eet\Api\Data\RecordInterface;
use Maghos\Eet\Model\DocumentContext;
use Maghos\Eet\Model\RecordInfo;

class Info
{
    /** @var RecordInfo */
    private RecordInfo $recordInfo;

    /** @var DocumentContext */
    private DocumentContext $documentContext;

    /**
     * @param RecordInfo $recordInfo
     * @param DocumentContext $documentContext
     */
    public function __construct(RecordInfo $recordInfo, DocumentContext $documentContext)
    {
        $this->recordInfo = $recordInfo;
        $this->documentContext = $documentContext;
    }

    /**
     * Append the EET receipt of the current invoice or credit memo, or all sale receipts of the order otherwise.
     *
     * @param InfoBlock $subject
     * @param array $info
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterGetSpecificInformation(InfoBlock $subject, $info): array
    {
        $info = (array)$info;
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $subject->getInfo();
        // order payments keep the order id in parent_id, quote payments have none
        $orderId = (int)$payment->getData('parent_id');
        if (!$orderId) {
            return $info;
        }

        [$field, $documentId] = $this->documentContext->getDocument($orderId)
            ?? [RecordInterface::ORDER_ID, $orderId];

        return array_merge($info, $this->recordInfo->getInfo($field, $documentId));
    }
}
