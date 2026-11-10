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

namespace Maghos\Eet\Controller\Adminhtml\Invoice;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction;
use Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Maghos\Eet\Api\Data\RecordInterface;
use Maghos\Eet\Model\MassResender;

class MassResend extends AbstractMassAction implements HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'Maghos_Eet::resend';

    /** @var MassResender */
    private MassResender $massResender;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param MassResender $massResender
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        MassResender $massResender
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->massResender = $massResender;
        $this->redirectUrl = 'sales/invoice/';
    }

    /**
     * Resend EET records of the selected invoices and report the result.
     *
     * @param AbstractCollection $collection
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $result = $this->massResender->resend(RecordInterface::INVOICE_ID, $collection->getAllIds());

        if ($result['sent']) {
            $this->messageManager->addSuccessMessage(__('%1 EET record(s) were sent.', $result['sent']));
        }
        if ($result['failed']) {
            $this->messageManager->addErrorMessage(
                __('%1 EET record(s) could not be sent.', $result['failed'])
            );
        }
        if (!$result['sent'] && !$result['failed']) {
            $this->messageManager->addNoticeMessage(__('The selected items have no unsent EET records.'));
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath($this->redirectUrl);
    }
}
