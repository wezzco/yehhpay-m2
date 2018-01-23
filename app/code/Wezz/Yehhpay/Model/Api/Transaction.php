<?php

namespace Wezz\Yehhpay\Model\Api;

use Wezz\Yehhpay\Model\Api\Client as Client;
use Wezz\Yehhpay\Model\Api\Data as Data;

/**
 * Class for working with data for Api Yehhpay
 *
 * Class Transaction
 */
class Transaction
{
    protected $client;
    protected $data;
    protected $transaction;
    protected $order;
    protected $request;

    /**
     * @param \Wezz\Yehhpay\Model\Api\Client $client
     * @param \Wezz\Yehhpay\Model\Api\Data $data
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        Client $client,
        Data $data,
        \Magento\Framework\App\Request\Http $request
    ) {

        $this->client = $client;
        $this->data = $data;
        $this->request = $request;

        $this->_init();
    }

    /**
     * Init method
     */
    public function _init()
    {
        $this->setTransaction();
    }

    public function transactionCreate()
    {
        /**
         * Call transaction create method
         */
        $data = $this->data->prepareDataForTransaction();

        $result = $this->client->callApi('Transaction.create', $data, true);

        /**
         * Set transactionId
         */
        if (isset($result['transactionId'])) {
            $this->data->saveTransactionId($result['transactionId']);
        }

        return $result;
    }

    /**
     * Method to view transaction status
     *
     * @param $transactionId
     * @return mixed
     */
    public function transactionView($transactionId)
    {
        if (!$transactionId) {
            return false;
        }

        $result = $this->client->callApi('Transaction.view', $transactionId);

        return $result;
    }

    /**
     * Method for transaction cancel
     *
     * @param $transactionId
     * @return mixed
     */
    public function transactionCancel($transactionId)
    {

        $result = $this->client->callApi('Transaction.cancel', $transactionId);
        return $result;
    }

    /**
     * Method for create refund
     *
     * @param $transactionId
     * @param $amount
     * @return mixed
     */
    public function refundCreate($transactionId, $amount)
    {
        $data = [$transactionId, $amount];
        $result = $this->client->callApi('Refund.create', $data);

        return $result;
    }

    /**
     * Callback notify listener from API
     *
     * @param $orderId
     * @return boolean
     */
    public function notify($orderId)
    {
        /**
         * Check current transaction status
         */
        $transactionResult = $this->checkCurrentTransaction($orderId);

        /**
         * Get transaction id
         */
        if ($orderId) {
            $transactionId = $this->data->getTransactionIdByIncrementId($orderId);
        }

        /**
         * Update status
         */
        $this->data->setOrderStatus($transactionResult, $orderId);

        /**
         * Create invoice
         */
        if ($transactionResult && $transactionId) {
            $this->data->createInvoice($orderId, $transactionId);
        }

        return $transactionResult;
    }

    /**
     * Callback notify listener from API
     *
     * @param $transactionId
     * @return boolean
     */
    public function hook($transactionId)
    {
        if (!$transactionId) {
            return false;
        }

        /**
         * Check current transaction status
         */
        $orderId = $this->data->getOrderByTransactionId($transactionId);

        $transactionResult = $this->checkCurrentTransaction($orderId, $transactionId);

        /**
         * Update status
         */
        $this->data->setOrderStatus($transactionResult, $orderId);

        if ($transactionResult) {
            $this->data->createInvoice($orderId, $transactionId);
        }

        return $transactionResult;
    }


    /**
     * Method to check suspended status of transaction
     *
     * and if transaction is suspended,
     * then call transaction resume method
     *
     * @param $orderId
     * @param $realOrderId
     * @return bool
     */
    public function checkTransactionIsSuspendedAndResume($orderId, $realOrderId)
    {
        /**
         * Get transaction id by orderId
         */
        $transactionId = $this->data->getTransactionIdByOrderId($orderId);

        if (!$transactionId) {
            return false;
        }

        /**
         * Make API request for transaction data
         */
        $result = $this->transactionView($transactionId);

        if (!isset($result['transactionId'])) {
            return false;
        }

        /**
         * Make API request to resume transaction
         */
        if (isset($result['state']['isSuspended']) && $result['state']['isSuspended']) {
            $this->transactionResume($transactionId, $realOrderId);
        } else {
            return false;
        }
    }

    /**
     * Method to check enabling status to refund transaction
     *
     * and if transaction is able to be refunded,
     * then call refund create method
     *
     * Transaction is able to refund only if consumer pay for transaction
     *
     * @param $orderId
     * @param $amount
     * @return bool
     */
    public function checkTransactionIsRefundAbleAndRefund($orderId, $amount)
    {
        /**
         * Get transaction id by orderId
         */
        $transactionId = $this->data->getTransactionIdByOrderId($orderId);

        if (!$transactionId) {
            return false;
        }

        /**
         * Make API request for transaction data
         */
        $result = $this->transactionView($transactionId);

        if (!isset($result['transactionId'])) {
            return false;
        }

        /**
         * Make API request to refund transaction
         */
        if ($result['state']['canCreateRefund'] && $amount > 0) {
            $this->refundCreate($transactionId, $amount);
        }
    }

    /**
     * Method to check current transaction
     *
     * @param $orderId
     * @param $transactionId
     * @return bool
     */
    private function checkCurrentTransaction($orderId, $transactionId = false)
    {
        if (!$transactionId) {
            $transactionId = $this->data->getTransactionIdByIncrementId($orderId);

            if (!$transactionId) {
                return false;
            }
        }

        /**
         * Make API request for transaction data
         */
        $result = $this->transactionView($transactionId);

        if (!isset($result['transactionId'])) {
            return false;
        }

        if (isset($result['state']['isOpen'])
            && isset($result['state']['hasBeenApprovedByConsumer'])
            && $result['state']['isOpen']
            && $result['state']['hasBeenApprovedByConsumer']) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Method to get current transaction
     *
     * @return int | boolean
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * Method to set current transaction
     *
     */
    public function setTransaction()
    {

        $orderId = $this->request->getParam('order_id');

        if (!$orderId) {
            return false;
        }

        /**
         * Get transaction id by order id
         */
        $transactionId = $this->data->getTransactionIdByOrderId($orderId);

        if (!$transactionId) {
            return false;
        }

        /**
         * Make API request for transaction data
         */
        $result = $this->transactionView($transactionId);

        if (!isset($result['transactionId'])) {
            return false;
        }

        $this->order = $this->data->getOrderById($orderId);

        /**
         * Prepare data array of current transaction
         */
        $this->transaction = $result;

        $this->transaction['orderId'] = $this->order->getRealOrderId();
        $this->transaction['resumeDate'] = $this->order->getPayment()->getYehhpayTransactionDate();
        $this->transaction['showCost'] = false;

        if (isset($result['state'])) {
            if (isset($result['state']['hasBeenApprovedByConsumer']) && $result['state']['hasBeenApprovedByConsumer']) {
                $this->transaction['consumerStatus'] = __("Approved by consumer and Yehhpay");
                $this->transaction['showCost'] = true;
            } else {
                $this->transaction['consumerStatus'] = __("Not approved by consumer or Yehhpay");
            }

            if (isset($result['state']['isCanceled']) && $result['state']['isCanceled']) {
                $this->transaction['stateStatus'] = __("Canceled");
            } elseif (isset($result['state']['isExpired']) && $result['state']['isExpired'] == true) {
                $this->transaction['stateStatus'] = __("Expired");
            } elseif (isset($result['state']['isSuspended']) && $result['state']['isSuspended'] == true) {
                $this->transaction['stateStatus'] = __("Suspended");
            }

            /**
             * Take calendar dates
             * Transaction can be suspended min to 1 day
             * and max to 7 day
             */
            $calendarDates = $this->data->getCalendarDates();

            $this->transaction['start'] = isset($calendarDates['start']) ? $calendarDates['start'] : '';
            $this->transaction['end'] = isset($calendarDates['end']) ? $calendarDates['end'] : '';

            if (isset($result['state']['canBeSuspended']) && $result['state']['canBeSuspended']) {
                $this->transaction['urlSuspend'] = $this->data->getUrl(
                    'yehhpay/transaction/suspend',
                    [
                        'id' => $result['transactionId'],
                        'orderId' => $orderId
                    ]
                );
            }

            if (isset($result['state']['isSuspended']) && $result['state']['isSuspended']) {
                $this->transaction['urlResume'] = $this->data->getUrl(
                    'yehhpay/transaction/resume',
                    [
                        'id' => $result['transactionId'],
                        'orderId' => $orderId
                    ]
                );
            }
        }
    }

    /**
     * Method for transaction suspend
     *
     * @param $transactionId
     * @param $suspendDate
     * @param $orderId
     * @return mixed
     */
    public function transactionSuspend($transactionId, $suspendDate, $orderId)
    {

        $data = [$transactionId, $suspendDate];

        $result = $this->client->callApi('Transaction.suspend', $data);

        if (isset($result['resumeDate'])) {
            $this->data->saveResumeDate($result['resumeDate'], $orderId);
        }

        return $result;
    }

    /**
     * Method for transaction suspend
     *
     * @param $transactionId
     * @param $orderId
     * @return mixed
     */
    public function transactionResume($transactionId, $orderId)
    {

        $result = $this->client->callApi('Transaction.resume', $transactionId);

        $this->data->saveResumeDate(null, $orderId);

        return $result;
    }
}
