<?php

namespace Wezz\Yehhpay\Observer;

use \Magento\Framework\Event\ObserverInterface;
use \Magento\Sales\Model\Order;

/**
 * Observer to listen credit memo save after event
 *
 * Class CreditMemoSaveAfter
 * @package Wezz\Yehhpay\Observer
 */
class CreditMemoSaveAfter implements ObserverInterface
{
    protected $transaction;

    /**
     * CreditMemoSaveAfter constructor.
     * @param \Wezz\Yehhpay\Model\Api\Transaction $transaction
     */
    public function __construct(
        \Wezz\Yehhpay\Model\Api\Transaction $transaction
    ) {
    
        $this->transaction = $transaction;
    }

    /**
     * Method to refund and cancel yehhpay transaction
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $creditMemo = $observer->getCreditmemo();

        if ($creditMemo) {
            $this->transaction->checkTransactionIsRefundAbleAndRefund(
                $creditMemo->getOrder()->getId(),
                (string) $creditMemo->getGrandTotal()
            );
        }
    }
}
