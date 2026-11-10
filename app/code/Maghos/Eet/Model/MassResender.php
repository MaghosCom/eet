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

use Magento\Framework\Api\SearchCriteriaBuilder;
use Maghos\Eet\Api\Data\RecordInterface;
use Maghos\Eet\Api\RecordRepositoryInterface;
use Maghos\Eet\Api\RecordSenderInterface;
use Psr\Log\LoggerInterface;

class MassResender
{
    /** @var RecordRepositoryInterface */
    private RecordRepositoryInterface $recordRepository;

    /** @var RecordSenderInterface */
    private RecordSenderInterface $recordSender;

    /** @var SearchCriteriaBuilder */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /** @var LoggerInterface */
    private LoggerInterface $logger;

    /**
     * @param RecordRepositoryInterface $recordRepository
     * @param RecordSenderInterface $recordSender
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param LoggerInterface $logger
     */
    public function __construct(
        RecordRepositoryInterface $recordRepository,
        RecordSenderInterface $recordSender,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        LoggerInterface $logger
    ) {
        $this->recordRepository = $recordRepository;
        $this->recordSender = $recordSender;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->logger = $logger;
    }

    /**
     * Resend unsent records of the invoices or credit memos and return sent and failed counts.
     *
     * @param string $field
     * @param array $ids
     * @return array
     */
    public function resend(string $field, array $ids): array
    {
        $result = ['sent' => 0, 'failed' => 0];
        if (!$ids) {
            return $result;
        }

        // rejected records are included too, they may pass once the certificate or config is fixed
        $this->searchCriteriaBuilder
            ->addFilter($field, $ids, 'in')
            ->addFilter(RecordInterface::STATUS, RecordInterface::STATUS_SENT, 'neq');

        foreach ($this->recordRepository->getList($this->searchCriteriaBuilder->create())->getItems() as $record) {
            try {
                $sent = $this->recordSender->resendRecord($record);
            } catch (\Throwable $e) {
                $this->logger->error('EET: resend of record ' . $record->getId() . ' failed: ' . $e->getMessage());
                $sent = false;
            }
            $result[$sent ? 'sent' : 'failed']++;
        }

        return $result;
    }
}
