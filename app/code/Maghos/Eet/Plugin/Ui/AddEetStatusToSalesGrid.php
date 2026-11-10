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

namespace Maghos\Eet\Plugin\Ui;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory;
use Maghos\Eet\Api\Data\RecordInterface;
use Maghos\Eet\Model\ResourceModel\Record;

class AddEetStatusToSalesGrid
{
    public const COLUMN = 'eet_status';
    private const INVOICE_GRID = 'sales_order_invoice_grid_data_source';
    private const CREDITMEMO_GRID = 'sales_order_creditmemo_grid_data_source';

    /**
     * Add the EET status subquery to the grid collection.
     *
     * @param CollectionFactory $subject
     * @param \Magento\Framework\Data\Collection $collection
     * @param string $requestName
     * @return \Magento\Framework\Data\Collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetReport(CollectionFactory $subject, $collection, $requestName)
    {
        if (!$collection instanceof AbstractDb
            || !in_array($requestName, [self::INVOICE_GRID, self::CREDITMEMO_GRID], true)
        ) {
            return $collection;
        }

        $field = $requestName === self::CREDITMEMO_GRID ? RecordInterface::CREDITMEMO_ID : RecordInterface::INVOICE_ID;
        $status = $collection->getConnection()->select()
            ->from(['eet' => $collection->getTable(Record::TABLE_NAME)], [RecordInterface::STATUS])
            ->where("eet.$field = main_table.entity_id");

        // a subquery instead of a join keeps grid filters on shared column names (created_at) unambiguous
        $expression = new \Zend_Db_Expr('(' . $status . ')');
        $collection->getSelect()->columns([self::COLUMN => $expression]);
        $collection->addFilterToMap(self::COLUMN, $expression);

        return $collection;
    }
}
