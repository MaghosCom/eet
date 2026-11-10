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

namespace Maghos\Eet\Plugin\Sales\Block\Order\Invoice;

use Magento\Sales\Block\Order\Invoice\Items as ItemsBlock;
use Maghos\Eet\Api\Data\RecordInterface;
use Maghos\Eet\Model\RecordInfo;

class Items
{
    /** @var RecordInfo */
    private RecordInfo $recordInfo;

    /**
     * @param RecordInfo $recordInfo
     */
    public function __construct(RecordInfo $recordInfo)
    {
        $this->recordInfo = $recordInfo;
    }

    /**
     * Append the EET receipt details of the invoice to its comments.
     *
     * @param ItemsBlock $subject
     * @param string $html
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return string
     */
    public function afterGetInvoiceCommentsHtml(ItemsBlock $subject, $html, $invoice): string
    {
        return $html . $this->recordInfo->getHtml(
            $subject->getLayout(),
            RecordInterface::INVOICE_ID,
            (int)$invoice->getId()
        );
    }
}
