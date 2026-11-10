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

namespace Maghos\Eet\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Maghos\Eet\Api\Data\RecordInterface;

class RecordStatus implements OptionSourceInterface
{
    /**
     * Get EET record statuses as options.
     *
     * @return array<int, array<string, mixed>>
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => RecordInterface::STATUS_SENT, 'label' => __('Sent')],
            ['value' => RecordInterface::STATUS_ERROR, 'label' => __('Failed, will be resent')],
            ['value' => RecordInterface::STATUS_REJECTED, 'label' => __('Rejected')],
            ['value' => RecordInterface::STATUS_NEW, 'label' => __('Not sent yet')],
        ];
    }
}
