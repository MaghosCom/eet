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

namespace Maghos\Eet\Block\System\Config\Form;

use Magento\Backend\Block\Widget\Button;

class TestButton extends Button
{
    /**
     * Define block template.
     *
     * @return void
     */
    protected function _construct(): void
    {
        parent::_construct();
        $this->setTemplate('Maghos_Eet::form/button.phtml');
    }
}
