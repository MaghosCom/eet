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

namespace Maghos\Eet\Plugin\Sales\Model\Order;

use Magento\Sales\Model\AbstractModel;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Maghos\Eet\Api\Data\RecordInterface;
use Maghos\Eet\Model\DocumentContext;

class RememberDocument
{
    /** @var DocumentContext */
    private DocumentContext $documentContext;

    /**
     * @param DocumentContext $documentContext
     */
    public function __construct(DocumentContext $documentContext)
    {
        $this->documentContext = $documentContext;
    }

    /**
     * Remember the document the order is reached through.
     *
     * @param AbstractModel $subject
     * @param Order $order
     * @return Order
     */
    public function afterGetOrder(AbstractModel $subject, $order)
    {
        if ($order->getId()) {
            $field = $subject instanceof Creditmemo ? RecordInterface::CREDITMEMO_ID : RecordInterface::INVOICE_ID;
            $documentId = $subject->getId() ? (int)$subject->getId() : null;
            $this->documentContext->setDocument((int)$order->getId(), $field, $documentId);
        }

        return $order;
    }
}
