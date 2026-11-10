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

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Maghos\Eet\Helper\Data as EetHelper;

class Payment implements OptionSourceInterface
{
    /** @var ScopeConfigInterface */
    private ScopeConfigInterface $scopeConfig;

    /** @var EetHelper */
    private EetHelper $eetHelper;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param EetHelper $eetHelper
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EetHelper $eetHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->eetHelper = $eetHelper;
    }

    /**
     * Get active payment methods as options.
     *
     * @return array<int, array<string, string>>
     */
    public function toOptionArray(): array
    {
        $options = [];
        foreach ($this->eetHelper->getActivePaymentMethods() as $code) {
            $options[] = [
                'label' => (string)$this->scopeConfig->getValue('payment/' . $code . '/title') ?: $code,
                'value' => $code,
            ];
        }

        return $options;
    }
}
