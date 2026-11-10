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

use Magento\Framework\Api\SearchResultsInterface;

interface RecordSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get record list.
     *
     * @return \Maghos\Eet\Api\Data\RecordInterface[]
     */
    public function getItems();

    /**
     * Set record list.
     *
     * @param \Maghos\Eet\Api\Data\RecordInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
