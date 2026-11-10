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

namespace Maghos\Eet\Plugin\Sales\Block\Order\PrintOrder;

use Magento\Sales\Block\Order\PrintOrder\Invoice as PrintBlock;
use Maghos\Eet\Api\Data\RecordInterface;
use Maghos\Eet\Model\DocumentContext;

class Invoice
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
     * Remember the printed invoice, its totals render before its payment info in the print loop.
     *
     * @param PrintBlock $subject
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetInvoiceTotalsHtml(PrintBlock $subject, $invoice): void
    {
        $this->documentContext->setDocument(
            (int)$invoice->getOrderId(),
            RecordInterface::INVOICE_ID,
            (int)$invoice->getId()
        );
    }

    /**
     * Render payment info without the layout output cache, so each printed invoice shows its own EET receipt.
     *
     * @param PrintBlock $subject
     * @param callable $proceed
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetPaymentInfoHtml(PrintBlock $subject, callable $proceed): string
    {
        return $subject->getChildHtml('payment_info', false);
    }
}
