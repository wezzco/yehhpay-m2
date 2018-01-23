<?php

namespace Wezz\Yehhpay\Controller\Transaction;

/**
 * Class Check
 *
 * Controller for check response from Yehhpay API
 *
 * @package Wezz\Yehhpay\Controller\Transaction
 */
class Check extends \Magento\Framework\App\Action\Action
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
     * Method to update order
     */
    public function execute()
    {
        $orderId = $this->getRequest()->get('order');

        if ($orderId) {
            /***
             * Check transaction status and update order
             */
            $result = $this->transaction->notify($orderId);

            if ($result) {
                /**
                 * Redirect to checkout success page
                 */
                $this->_redirect('checkout/onepage/success');
            } else {
                /**
                 * Redirect to checkout cart
                 */
                $this->data->returnOldQuote();
                $this->_redirect('checkout/cart');
            }
        }
    }
}
