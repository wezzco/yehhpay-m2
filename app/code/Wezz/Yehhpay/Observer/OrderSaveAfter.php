<?php

namespace Wezz\Yehhpay\Observer;

use \Magento\Framework\Event\ObserverInterface;
use \Magento\Sales\Model\Order;

/**
 * Observer to listen order save after event
 *
 * Class OrderSaveAfter
 * @package Wezz\Yehhpay\Observer
 */
class OrderSaveAfter implements ObserverInterface
{
    protected $transaction;

    /**
     * OrderSaveAfter constructor.
     * @param \Wezz\Yehhpay\Model\Api\Transaction $transaction
     */
    public function __construct(
        \Wezz\Yehhpay\Model\Api\Transaction $transaction
    ) {
    
        $this->transaction = $transaction;
    }

    /**
     * Method to resume transaction
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();

        if ($order->getState() == Order::STATE_COMPLETE) {
            $this->transaction->checkTransactionIsSuspendedAndResume(
                $order->getId(),
                $order->getRealOrderId()
            );
        }
    }
}
