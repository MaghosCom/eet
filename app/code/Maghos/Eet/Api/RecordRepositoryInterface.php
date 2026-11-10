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

use Magento\Framework\Api\SearchCriteriaInterface;
use Maghos\Eet\Api\Data\RecordInterface;
use Maghos\Eet\Api\Data\RecordSearchResultsInterface;

interface RecordRepositoryInterface
{
    /**
     * Create new record model.
     *
     * @return RecordInterface
     */
    public function create(): RecordInterface;

    /**
     * Save record entity.
     *
     * @param RecordInterface $record
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(RecordInterface $record): void;

    /**
     * Delete record entity.
     *
     * @param RecordInterface $record
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(RecordInterface $record): void;

    /**
     * Delete record entity by id.
     *
     * @param int $recordId
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteById(int $recordId): void;

    /**
     * Get record by id.
     *
     * @param int $recordId
     * @return RecordInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get(int $recordId): RecordInterface;

    /**
     * Retrieve records matching the search criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return RecordSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): RecordSearchResultsInterface;

    /**
     * Get the first record by an id field (order, invoice or credit memo) and refund flag, null when none exists.
     *
     * @param string $field
     * @param int $value
     * @param bool $refund
     * @return RecordInterface|null
     */
    public function getRecordBy(string $field, int $value, bool $refund = false): ?RecordInterface;
}
