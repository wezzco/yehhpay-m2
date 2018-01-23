<?php

namespace Wezz\Yehhpay\Controller\Adminhtml\Transaction;

use Magento\Framework\Controller\ResultFactory;

/**
 * Controller to resume transaction
 *
 * Class Resume
 * @package Wezz\Yehhpay\Controller\Adminhtml\Transaction
 */
class Resume extends \Magento\Framework\App\Action\Action
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
     * Method to resume transaction
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $transactionId = $this->getRequest()->get('id');
        $orderId = $this->getRequest()->get('orderId');

        /**
         * Prepare result redirect
         */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        if (!$transactionId || !$orderId) {
            return $resultRedirect;
        }

        $this->transaction->transactionResume($transactionId, $orderId);

        return $resultRedirect;
    }
}
