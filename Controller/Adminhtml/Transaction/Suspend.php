<?php

namespace Wezz\Yehhpay\Controller\Adminhtml\Transaction;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class Suspend
 *
 * Controller to suspend transaction
 *
 * @package Wezz\Yehhpay\Controller\Adminhtml\Transaction
 */
class Suspend extends \Magento\Framework\App\Action\Action
{
    protected $transaction;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Wezz\Yehhpay\Model\Api\Transaction $transaction
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Wezz\Yehhpay\Model\Api\Transaction $transaction
    ) {
        $this->transaction = $transaction;

        parent::__construct($context);
    }

    /**
     * Method to suspend transaction
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $transactionId = $this->getRequest()->get('id');
        $orderId = $this->getRequest()->get('orderId');
        $suspendDate = $this->getRequest()->getPost('suspendDate');

        /**
         * Result redirect prepare
         */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        if (!$transactionId || !$orderId || !$suspendDate) {
            return $resultRedirect;
        }

        $this->transaction->transactionSuspend($transactionId, $suspendDate, $orderId);

        return $resultRedirect;
    }
}
