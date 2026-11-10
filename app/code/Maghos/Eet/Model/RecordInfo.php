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
use Magento\Framework\View\LayoutInterface;
use Maghos\Eet\Api\Data\RecordInterface;
use Maghos\Eet\Api\RecordRepositoryInterface;
use Maghos\Eet\Block\Payment\Info\Specific;

class RecordInfo
{
    /** @var RecordRepositoryInterface */
    private RecordRepositoryInterface $recordRepository;

    /** @var SearchCriteriaBuilder */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @param RecordRepositoryInterface $recordRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        RecordRepositoryInterface $recordRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->recordRepository = $recordRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Get receipt details as label => list of values, by a record id field; by order only sale receipts are listed.
     *
     * @param string $field
     * @param int $value
     * @return array
     */
    public function getInfo(string $field, int $value): array
    {
        $this->searchCriteriaBuilder->addFilter($field, $value);
        if ($field === RecordInterface::ORDER_ID) {
            $this->searchCriteriaBuilder->addFilter(RecordInterface::IS_REFUND, 0);
        }

        // values are lists, payment info templates print each receipt of the order on its own line
        $info = [];
        foreach ($this->recordRepository->getList($this->searchCriteriaBuilder->create())->getItems() as $record) {
            $values = [
                'POK' => (string)$record->getPok(),
                'EIC' => $record->getEic(),
                'Registrating Unit ID' => $record->getUnitId(),
                'Checkout ID' => $record->getRegisterId(),
                'Receipt number' => $record->getReceiptNumber(),
            ];
            foreach ($values as $label => $item) {
                $info[(string)__($label)][] = $item;
            }
        }

        return $info;
    }

    /**
     * Render receipt details of the document for the storefront, empty when it has no record.
     *
     * @param LayoutInterface $layout
     * @param string $field
     * @param int $value
     * @return string
     */
    public function getHtml(LayoutInterface $layout, string $field, int $value): string
    {
        $info = $this->getInfo($field, $value);
        if (!$info) {
            return '';
        }

        return $layout->createBlock(Specific::class, '', ['data' => [
            'template' => 'Maghos_Eet::info/information.phtml',
            'specific_information' => $info,
        ]])->toHtml();
    }
}
