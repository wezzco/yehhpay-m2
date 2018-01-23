<?php

namespace Wezz\Yehhpay\Block\Adminhtml\Order\View;

/**
 * Class Info
 *
 * Block class to render Yehhpay tranasction information at Order View
 *
 * @package Wezz\Yehhpay\Block\Adminhtml\Order\View
 */
class Info extends \Magento\Backend\Block\Template
{
    protected $transaction;
    protected $transactionModel;

    /**
     * Info constructor.
     *
     * @param \Wezz\Yehhpay\Model\Api\Transaction $transactionModel
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Wezz\Yehhpay\Model\Api\Transaction $transactionModel,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
    
        $this->transactionModel = $transactionModel;

        $this->_init();
        parent::__construct($context, $data);
    }

    /**
     * Init method
     */
    public function _init()
    {
        $this->transaction = $this->transactionModel->getTransaction();
    }

    /**
     * Method to get transaction id
     * @return string
     */
    public function getTransactionId()
    {
        return isset($this->transaction['transactionId']) ? $this->transaction['transactionId'] : '';
    }

    /**
     * Method to get consumer status
     *
     * @return string
     */
    public function getConsumerStatus()
    {
        return isset($this->transaction['consumerStatus']) ? $this->transaction['consumerStatus'] : '';
    }

    /**
     * Method to get state status
     *
     * @return string
     */
    public function getStateStatus()
    {
        return isset($this->transaction['stateStatus']) ? $this->transaction['stateStatus'] : '';
    }

    /**
     * Method to get cost
     *
     * @return string
     */
    public function getCost()
    {
        return isset($this->transaction['order']['cost']) ? $this->transaction['order']['cost'] : '';
    }

    /**
     * Method to get url resume
     *
     * @return string
     */
    public function getUrlResume()
    {
        return isset($this->transaction['urlResume']) ? $this->transaction['urlResume'] : '';
    }

    /**
     * Method to get url suspend
     *
     * @return string
     */
    public function getUrlSuspend()
    {
        return isset($this->transaction['urlSuspend']) ? $this->transaction['urlSuspend'] : '';
    }

    /**
     * Method to get resume date
     *
     * @return string
     */
    public function getResumeDate()
    {
        return isset($this->transaction['resumeDate']) ? $this->transaction['resumeDate'] : '';
    }

    /**
     * Method to get start date
     *
     * @return string
     */
    public function getStart()
    {
        return isset($this->transaction['start']) ? $this->transaction['start'] : '';
    }

    /**
     * Method to get end date
     *
     * @return string
     */
    public function getEnd()
    {
        return isset($this->transaction['end']) ? $this->transaction['end'] : '';
    }

    public function getShowCost()
    {
        return isset($this->transaction['showCost']) ? $this->transaction['showCost'] : '';
    }
}
