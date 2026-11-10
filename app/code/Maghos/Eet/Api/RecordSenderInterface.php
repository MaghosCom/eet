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

namespace Maghos\Eet\Api;

use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Maghos\Eet\Api\Data\RecordInterface;

interface RecordSenderInterface
{
    /**
     * Create the sale record for the invoice and send it to EET.
     *
     * @param InvoiceInterface $invoice
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function sendInvoice(InvoiceInterface $invoice): bool;

    /**
     * Create the refund record for the credit memo and send it to EET.
     *
     * @param CreditmemoInterface $creditmemo
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function sendCreditmemo(CreditmemoInterface $creditmemo): bool;

    /**
     * Send an existing record again with unchanged sale data.
     *
     * @param RecordInterface $record
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function resendRecord(RecordInterface $record): bool;

    /**
     * Send a verification mode message with the configuration of the scope and return result messages.
     *
     * @param string|null $scopeCode
     * @param bool $websiteScope
     * @return array
     */
    public function eetConfigTest(?string $scopeCode = null, bool $websiteScope = false): array;
}
