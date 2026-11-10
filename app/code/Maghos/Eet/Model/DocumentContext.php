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

class DocumentContext
{
    /** @var array */
    private array $documents = [];

    /**
     * Remember the record id field and id of the document (invoice, credit memo) the order was last reached through.
     *
     * @param int $orderId
     * @param string $field
     * @param int|null $documentId
     * @return void
     */
    public function setDocument(int $orderId, string $field, ?int $documentId): void
    {
        $this->documents[$orderId] = $documentId ? [$field, $documentId] : null;
    }

    /**
     * Get the record id field and id of the document of the order, null outside a saved document.
     *
     * @param int $orderId
     * @return array|null
     */
    public function getDocument(int $orderId): ?array
    {
        return $this->documents[$orderId] ?? null;
    }
}
