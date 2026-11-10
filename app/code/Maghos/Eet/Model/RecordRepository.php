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

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Maghos\Eet\Api\Data\RecordInterface;
use Maghos\Eet\Api\Data\RecordSearchResultsInterface;
use Maghos\Eet\Api\Data\RecordSearchResultsInterfaceFactory;
use Maghos\Eet\Api\RecordRepositoryInterface;
use Maghos\Eet\Model\ResourceModel\Record as RecordResource;
use Maghos\Eet\Model\ResourceModel\Record\CollectionFactory;

class RecordRepository implements RecordRepositoryInterface
{
    /** @var RecordFactory */
    private RecordFactory $recordFactory;

    /** @var CollectionFactory */
    private CollectionFactory $collectionFactory;

    /** @var RecordResource */
    private RecordResource $recordResource;

    /** @var RecordSearchResultsInterfaceFactory */
    private RecordSearchResultsInterfaceFactory $searchResultsFactory;

    /** @var SearchCriteriaBuilder */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /** @var CollectionProcessorInterface */
    private CollectionProcessorInterface $collectionProcessor;

    /**
     * @param RecordFactory $recordFactory
     * @param CollectionFactory $collectionFactory
     * @param RecordResource $recordResource
     * @param RecordSearchResultsInterfaceFactory $searchResultsFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        RecordFactory $recordFactory,
        CollectionFactory $collectionFactory,
        RecordResource $recordResource,
        RecordSearchResultsInterfaceFactory $searchResultsFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->recordFactory = $recordFactory;
        $this->collectionFactory = $collectionFactory;
        $this->recordResource = $recordResource;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function create(): RecordInterface
    {
        return $this->recordFactory->create();
    }

    /**
     * @inheritDoc
     */
    public function save(RecordInterface $record): void
    {
        try {
            /** @var Record $record */
            $this->recordResource->save($record);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save the EET record: %1', $e->getMessage()), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(RecordInterface $record): void
    {
        try {
            /** @var Record $record */
            $this->recordResource->delete($record);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete the EET record: %1', $e->getMessage()), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function deleteById(int $recordId): void
    {
        $this->delete($this->get($recordId));
    }

    /**
     * @inheritDoc
     */
    public function get(int $recordId): RecordInterface
    {
        /** @var Record $record */
        $record = $this->create();
        $this->recordResource->load($record, $recordId);

        if (!$record->getId()) {
            throw NoSuchEntityException::singleField(RecordInterface::RECORD_ID, $recordId);
        }

        return $record;
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): RecordSearchResultsInterface
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var RecordSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        /** @var RecordInterface[] $items */
        $items = $collection->getItems();
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function getRecordBy(string $field, int $value, bool $refund = false): ?RecordInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter($field, $value)
            ->addFilter(RecordInterface::IS_REFUND, (int)$refund)
            ->setPageSize(1)
            ->create();

        $items = $this->getList($searchCriteria)->getItems();
        return $items ? reset($items) : null;
    }
}
