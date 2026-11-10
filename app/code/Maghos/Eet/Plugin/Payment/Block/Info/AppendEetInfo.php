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

namespace Maghos\Eet\Plugin\Payment\Block\Info;

use Magento\Payment\Block\Info;
use Magento\Payment\Block\Info\Instructions;
use Maghos\Eet\Block\Payment\Info\Specific;

/**
 * Renders EET details below payment info blocks whose templates do not show specific information.
 */
class AppendEetInfo
{
    /** @var bool */
    private bool $isCreatingPdf = false;

    /**
     * Append the EET information block to the payment info HTML.
     *
     * @param Info $subject
     * @param string $html
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterToHtml(Info $subject, $html): string
    {
        // the default PDF template of Instructions renders specific information already
        if ($this->isCreatingPdf && $subject instanceof Instructions) {
            return (string)$html;
        }

        $block = $subject->getLayout()->createBlock(Specific::class);
        $block->setTemplate(
            $this->isCreatingPdf ? 'Maghos_Eet::info/pdf/information.phtml' : 'Maghos_Eet::info/information.phtml'
        );
        $block->setData('specific_information', $subject->getSpecificInformation());

        return $html . $block->toHtml();
    }

    /**
     * Mark that PDF output is being rendered.
     *
     * @param Info $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeToPdf(Info $subject): void
    {
        $this->isCreatingPdf = true;
    }

    /**
     * Reset the PDF output flag.
     *
     * @param Info $subject
     * @param string $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterToPdf(Info $subject, $result): string
    {
        $this->isCreatingPdf = false;
        return (string)$result;
    }
}
