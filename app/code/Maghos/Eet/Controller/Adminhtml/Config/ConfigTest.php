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

namespace Maghos\Eet\Controller\Adminhtml\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Maghos\Eet\Api\RecordSenderInterface;

class ConfigTest extends Action implements HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'Maghos_Eet::manage_eet';

    /** @var RecordSenderInterface */
    private RecordSenderInterface $recordSender;

    /** @var JsonFactory */
    private JsonFactory $resultJsonFactory;

    /**
     * @param Context $context
     * @param RecordSenderInterface $recordSender
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        RecordSenderInterface $recordSender,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->recordSender = $recordSender;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Run the EET configuration test for the requested scope and return messages as JSON.
     *
     * @return Json
     */
    public function execute(): Json
    {
        $code = $this->getRequest()->getParam('store');
        $website = false;
        if (!$code) {
            $code = $this->getRequest()->getParam('website');
            $website = (bool)$code;
        }

        $messages = $this->recordSender->eetConfigTest($code ? (string)$code : null, $website);

        return $this->resultJsonFactory->create()->setData([
            'messages' => array_map('strval', $messages),
            'title' => (string)__('EET Configuration Test'),
        ]);
    }
}
