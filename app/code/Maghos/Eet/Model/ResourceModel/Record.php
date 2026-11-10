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

namespace Maghos\Eet\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Maghos\Eet\Api\Data\RecordInterface;

class Record extends AbstractDb
{
    public const TABLE_NAME = 'maghos_eet_record';

    /**
     * Initialize main table and id field.
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(self::TABLE_NAME, RecordInterface::RECORD_ID);
    }
}
