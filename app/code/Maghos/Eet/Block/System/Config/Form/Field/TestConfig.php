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

namespace Maghos\Eet\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Maghos\Eet\Block\System\Config\Form\TestButton;

class TestConfig extends Field
{
    /**
     * Render the "Test Config" button for the current config scope.
     *
     * @param AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $params = array_filter([
            'store' => $this->_request->getParam('store'),
            'website' => $this->_request->getParam('website'),
        ]);

        return $this->getLayout()->createBlock(TestButton::class)
            ->setData([
                'label' => __('Test Config'),
                'value' => $this->getUrl('imaghoseet/config/configTest', $params),
                'class' => 'eet-test',
            ])
            ->toHtml();
    }
}
