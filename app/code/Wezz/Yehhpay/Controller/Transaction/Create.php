<?php

namespace Wezz\Yehhpay\Controller\Transaction;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class Create
 *
 * Controller to create Yehhpay API transaction
 *
 * @package Wezz\Yehhpay\Controller\Transaction
 */
class Create extends \Magento\Framework\App\Action\Action
{
    protected $transaction;
    protected $data;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Wezz\Yehhpay\Model\Api\Transaction $transaction
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Wezz\Yehhpay\Model\Api\Transaction $transaction,
        \Wezz\Yehhpay\Model\Api\Data $data
    ) {
        $this->transaction = $transaction;
        $this->data = $data;

        parent::__construct($context);
    }

    /**
     * Method to create transaction
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /**
         * Provide transaction
         */
        $result = $this->transaction->transactionCreate();

        /**
         * If transaction has success status,
         * redirect to external yehh pay part
         * else - set payment failed status and payment review state comment
         * and redirect to order page success
         */
        if (isset($result['isSuccess']) && $result['isSuccess'] && isset($result['url']) && $result['url']) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($result['url']);
            return $resultRedirect;
        } else {
            $this->data->returnOldQuote();
            $this->_redirect('checkout/cart');
        }
    }
}
