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

namespace Maghos\Eet\Api\Data;

interface RecordInterface
{
    public const RECORD_ID = 'id';
    public const ORDER_ID = 'order_id';
    public const INVOICE_ID = 'invoice_id';
    public const CREDITMEMO_ID = 'creditmemo_id';
    public const EIC = 'eic';
    public const UNIT_ID = 'unit_id';
    public const REGISTER_ID = 'register_id';
    public const RECEIPT_NUMBER = 'receipt_number';
    public const AMOUNT = 'amount';
    public const POK = 'pok';
    public const STATUS = 'status';
    public const IS_REFUND = 'is_refund';
    public const CREATED_AT = 'created_at';

    public const STATUS_NEW = 0;
    public const STATUS_ERROR = 1;
    public const STATUS_SENT = 2;
    public const STATUS_REJECTED = 3;

    /**
     * Get record id.
     *
     * @return int|string|null
     */
    public function getId();

    /**
     * Set record id.
     *
     * @param int|string|null $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get order id.
     *
     * @return int|null
     */
    public function getOrderId(): ?int;

    /**
     * Set order id.
     *
     * @param int|null $orderId
     * @return RecordInterface
     */
    public function setOrderId(?int $orderId): RecordInterface;

    /**
     * Get invoice id.
     *
     * @return int|null
     */
    public function getInvoiceId(): ?int;

    /**
     * Set invoice id.
     *
     * @param int|null $invoiceId
     * @return RecordInterface
     */
    public function setInvoiceId(?int $invoiceId): RecordInterface;

    /**
     * Get credit memo id.
     *
     * @return int|null
     */
    public function getCreditmemoId(): ?int;

    /**
     * Set credit memo id.
     *
     * @param int|null $creditmemoId
     * @return RecordInterface
     */
    public function setCreditmemoId(?int $creditmemoId): RecordInterface;

    /**
     * Get taxpayer EIC.
     *
     * @return string
     */
    public function getEic(): string;

    /**
     * Set taxpayer EIC.
     *
     * @param string $eic
     * @return RecordInterface
     */
    public function setEic(string $eic): RecordInterface;

    /**
     * Get registrating unit id.
     *
     * @return string
     */
    public function getUnitId(): string;

    /**
     * Set registrating unit id.
     *
     * @param string $unitId
     * @return RecordInterface
     */
    public function setUnitId(string $unitId): RecordInterface;

    /**
     * Get cash register (PoS) id.
     *
     * @return string
     */
    public function getRegisterId(): string;

    /**
     * Set cash register (PoS) id.
     *
     * @param string $registerId
     * @return RecordInterface
     */
    public function setRegisterId(string $registerId): RecordInterface;

    /**
     * Get receipt sequence number.
     *
     * @return string
     */
    public function getReceiptNumber(): string;

    /**
     * Set receipt sequence number.
     *
     * @param string $receiptNumber
     * @return RecordInterface
     */
    public function setReceiptNumber(string $receiptNumber): RecordInterface;

    /**
     * Get total amount in CZK.
     *
     * @return float
     */
    public function getAmount(): float;

    /**
     * Set total amount in CZK.
     *
     * @param float $amount
     * @return RecordInterface
     */
    public function setAmount(float $amount): RecordInterface;

    /**
     * Get acknowledgement code (POK).
     *
     * @return string|null
     */
    public function getPok(): ?string;

    /**
     * Set acknowledgement code (POK).
     *
     * @param string|null $pok
     * @return RecordInterface
     */
    public function setPok(?string $pok): RecordInterface;

    /**
     * Get status.
     *
     * @return int
     */
    public function getStatus(): int;

    /**
     * Set status.
     *
     * @param int $status
     * @return RecordInterface
     */
    public function setStatus(int $status): RecordInterface;

    /**
     * Get refund flag.
     *
     * @return bool
     */
    public function isRefund(): bool;

    /**
     * Set refund flag.
     *
     * @param bool $isRefund
     * @return RecordInterface
     */
    public function setIsRefund(bool $isRefund): RecordInterface;

    /**
     * Get sale date in UTC.
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * Set sale date in UTC.
     *
     * @param string|null $createdAt
     * @return RecordInterface
     */
    public function setCreatedAt(?string $createdAt): RecordInterface;
}
