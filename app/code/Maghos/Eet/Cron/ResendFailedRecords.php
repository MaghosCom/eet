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

namespace Maghos\Eet\Cron;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Maghos\Eet\Api\Data\RecordInterface;
use Maghos\Eet\Api\RecordRepositoryInterface;
use Maghos\Eet\Api\RecordSenderInterface;
use Psr\Log\LoggerInterface;

class ResendFailedRecords
{
    /** @var LoggerInterface */
    private LoggerInterface $logger;

    /** @var RecordRepositoryInterface */
    private RecordRepositoryInterface $recordRepository;

    /** @var RecordSenderInterface */
    private RecordSenderInterface $recordSender;

    /** @var SearchCriteriaBuilder */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @param LoggerInterface $logger
     * @param RecordRepositoryInterface $recordRepository
     * @param RecordSenderInterface $recordSender
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        LoggerInterface $logger,
        RecordRepositoryInterface $recordRepository,
        RecordSenderInterface $recordSender,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->logger = $logger;
        $this->recordRepository = $recordRepository;
        $this->recordSender = $recordSender;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Resend failed EET records; they exist only where EET was enabled, and must be sent even if disabled since.
     *
     * @return void
     */
    public function execute(): void
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(RecordInterface::STATUS, RecordInterface::STATUS_ERROR)
            ->addFilter(RecordInterface::ORDER_ID, true, 'notnull')
            ->create();

        $successes = 0;
        $failures = 0;
        foreach ($this->recordRepository->getList($searchCriteria)->getItems() as $record) {
            try {
                $sent = $this->recordSender->resendRecord($record);
            } catch (\Throwable $e) {
                $this->logger->error('EET: resend of record ' . $record->getId() . ' failed: ' . $e->getMessage());
                $sent = false;
            }
            $sent ? $successes++ : $failures++;
        }

        $total = $successes + $failures;
        $this->logger->info(
            "EET cron ran, $successes/$total receipts successfully revalidated, "
            . "$failures/$total receipts remain unvalidated."
        );
    }
}
