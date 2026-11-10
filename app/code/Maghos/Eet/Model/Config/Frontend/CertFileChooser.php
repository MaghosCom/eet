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

namespace Maghos\Eet\Model\Config\Frontend;

use Magento\Framework\Data\Form\Element\File;

class CertFileChooser extends File
{
    /**
     * Get element HTML with the delete checkbox.
     *
     * @return string
     */
    public function getElementHtml(): string
    {
        return parent::getElementHtml() . $this->getDeleteCheckbox();
    }

    /**
     * Get HTML of the delete checkbox shown when a certificate is uploaded.
     *
     * @return string
     */
    private function getDeleteCheckbox(): string
    {
        if (!$this->getValue()) {
            return '';
        }

        $id = $this->_escaper->escapeHtmlAttr($this->getHtmlId() . '_delete');
        $disabled = (bool)$this->getDisabled();

        return '<div> ' . $this->_escaper->escapeHtml(__('Cert file chosen.')) . ' '
            . '<input type="checkbox" name="' . $this->_escaper->escapeHtmlAttr(parent::getName()) . '[delete]"'
            . ' value="1" class="checkbox" id="' . $id . '"' . ($disabled ? ' disabled="disabled"' : '') . '/>'
            . '<label for="' . $id . '"' . ($disabled ? ' class="disabled"' : '') . '> '
            . $this->_escaper->escapeHtml(__('Delete File')) . '</label>'
            . '</div>';
    }
}
