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

use Magento\Framework\Model\AbstractModel;
use Maghos\Eet\Api\Data\RecordInterface;

class Record extends AbstractModel implements RecordInterface
{
    /**
     * Initialize resource model.
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(ResourceModel\Record::class);
    }

    /**
     * @inheritDoc
     */
    public function getOrderId(): ?int
    {
        $value = $this->getData(self::ORDER_ID);
        return $value === null ? null : (int)$value;
    }

    /**
     * @inheritDoc
     */
    public function setOrderId(?int $orderId): RecordInterface
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @inheritDoc
     */
    public function getInvoiceId(): ?int
    {
        $value = $this->getData(self::INVOICE_ID);
        return $value === null ? null : (int)$value;
    }

    /**
     * @inheritDoc
     */
    public function setInvoiceId(?int $invoiceId): RecordInterface
    {
        return $this->setData(self::INVOICE_ID, $invoiceId);
    }

    /**
     * @inheritDoc
     */
    public function getCreditmemoId(): ?int
    {
        $value = $this->getData(self::CREDITMEMO_ID);
        return $value === null ? null : (int)$value;
    }

    /**
     * @inheritDoc
     */
    public function setCreditmemoId(?int $creditmemoId): RecordInterface
    {
        return $this->setData(self::CREDITMEMO_ID, $creditmemoId);
    }

    /**
     * @inheritDoc
     */
    public function getEic(): string
    {
        return (string)$this->getData(self::EIC);
    }

    /**
     * @inheritDoc
     */
    public function setEic(string $eic): RecordInterface
    {
        return $this->setData(self::EIC, $eic);
    }

    /**
     * @inheritDoc
     */
    public function getUnitId(): string
    {
        return (string)$this->getData(self::UNIT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setUnitId(string $unitId): RecordInterface
    {
        return $this->setData(self::UNIT_ID, $unitId);
    }

    /**
     * @inheritDoc
     */
    public function getRegisterId(): string
    {
        return (string)$this->getData(self::REGISTER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setRegisterId(string $registerId): RecordInterface
    {
        return $this->setData(self::REGISTER_ID, $registerId);
    }

    /**
     * @inheritDoc
     */
    public function getReceiptNumber(): string
    {
        return (string)$this->getData(self::RECEIPT_NUMBER);
    }

    /**
     * @inheritDoc
     */
    public function setReceiptNumber(string $receiptNumber): RecordInterface
    {
        return $this->setData(self::RECEIPT_NUMBER, $receiptNumber);
    }

    /**
     * @inheritDoc
     */
    public function getAmount(): float
    {
        return (float)$this->getData(self::AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setAmount(float $amount): RecordInterface
    {
        return $this->setData(self::AMOUNT, $amount);
    }

    /**
     * @inheritDoc
     */
    public function getPok(): ?string
    {
        $value = $this->getData(self::POK);
        return $value === null ? null : (string)$value;
    }

    /**
     * @inheritDoc
     */
    public function setPok(?string $pok): RecordInterface
    {
        return $this->setData(self::POK, $pok);
    }

    /**
     * @inheritDoc
     */
    public function getStatus(): int
    {
        return (int)$this->getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus(int $status): RecordInterface
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritDoc
     */
    public function isRefund(): bool
    {
        return (bool)$this->getData(self::IS_REFUND);
    }

    /**
     * @inheritDoc
     */
    public function setIsRefund(bool $isRefund): RecordInterface
    {
        return $this->setData(self::IS_REFUND, (int)$isRefund);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): ?string
    {
        $value = $this->getData(self::CREATED_AT);
        return $value === null ? null : (string)$value;
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(?string $createdAt): RecordInterface
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}
