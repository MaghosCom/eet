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

namespace Maghos\Eet\Model\ResourceModel\Record;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Maghos\Eet\Api\Data\RecordInterface;
use Maghos\Eet\Model\Record;
use Maghos\Eet\Model\ResourceModel\Record as RecordResource;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = RecordInterface::RECORD_ID;

    /**
     * Initialize model and resource model.
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(Record::class, RecordResource::class);
    }
}
