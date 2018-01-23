<?php

namespace Wezz\Yehhpay\Controller\Transaction;

/**
 * Class Notify
 *
 * Controller to listen transaction changes from Yehhpay API
 *
 * @package Wezz\Yehhpay\Controller\Transaction
 */
class Notify extends \Magento\Framework\App\Action\Action
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
     * Method to update order
     */
    public function execute()
    {
        if (null !== filter_input(INPUT_POST, 'transactionId')) {

            throw new \Exception('Missing transaction id in notification callback.');
        }

        $transactionId = (int) filter_input(INPUT_POST, 'transactionId');

        if ($transactionId) {
            /***
             * Check transaction status and update order
             */
            $this->transaction->hook($transactionId);
        }
    }
}
