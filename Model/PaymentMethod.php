<?php

namespace Wezz\Yehhpay\Model;

use \Magento\Backend\Model\UrlInterface;
use Magento\Checkout\Model\Session;

/**
 * Class PaymentMethod
 * @package Wezz\Yehhpay\Model
 */
class PaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $_code = 'yehhpay';

    protected $_isInitializeNeeded      = true;
    protected $_canCapture              = true;
    protected $_canUseInternal          = false;
    protected $_canUseForMultishipping  = false;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;

    protected $_urlBuilder;
    protected $_scopeConfig;
    protected $_scopeStore;
    protected $_session;

    /**
     * PaymentMethod constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Checkout\Model\Session $session,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
    
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );

        $this->_scopeConfig = $scopeConfig;
        $this->_scopeStore = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $this->_urlBuilder = $urlBuilder;
        $this->_session = $session;
    }

    /**
     * Method to check is available status
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        $active = $this->_scopeConfig->getValue(
            'yehhpay/yehhpay/active',
            $this->_scopeStore
        );

        if (!$active) {
            return false;
        }

        /**
         * Check for min and max order total
         */
        $baseGrandTotal = $quote->getBaseGrandTotal();
        $minOrderTotal = $this->_scopeConfig->getValue(
            'yehhpay/yehhpay/min_order_total',
            $this->_scopeStore
        );

        $maxOrderTotal = $this->_scopeConfig->getValue(
            'yehhpay/yehhpay/max_order_total',
            $this->_scopeStore
        );

        if ($minOrderTotal !== '' && $minOrderTotal !== null && $baseGrandTotal < $minOrderTotal) {
            return false;
        }

        if ($maxOrderTotal !== '' && $maxOrderTotal !== null && $baseGrandTotal > $maxOrderTotal) {
            return false;
        }

        $specificCountry = $this->_scopeConfig->getValue(
            'yehhpay/advanced/specificcountry',
            $this->_scopeStore
        );

        if ($specificCountry) {
            $specificCountryArray = explode(',', $specificCountry);
            $shippingCountry = $quote->getShippingAddress()->getCountry();

            if (!in_array($shippingCountry, $specificCountryArray)) {
                return false;
            }
        }


        return parent::isAvailable($quote);
    }

    /**
     * Method to validate payment method
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate()
    {
        $checkAddress = $this->_scopeConfig->getValue(
            'yehhpay/advanced/check_address',
            $this->_scopeStore
        );

        if ($checkAddress) {
            $check = $this->_session->getQuote()->getShippingAddress()->getSameAsBilling();

            if (!$check) {
                $billingAddress = $this->_session->getQuote()->getBillingAddress();
                $shippingAddress = $this->_session->getQuote()->getShippingAddress();

                $shippingData = $this->serializeAddress($shippingAddress);
                $billingData = $this->serializeAddress($billingAddress);

                if (strcmp($shippingData, $billingData) != 0) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Yehhpay requires that your billing and shipping address are the same.')
                    );
                }
            }
        }

        return parent::validate();
    }

    /**
     * Method to get serialed address array
     *
     * @param $address
     * @return string
     */
    function serializeAddress($address)
    {
        return serialize(
            [
                'firstname' => $address->getFirstname(),
                'lastname'  => $address->getLastname(),
                'street'    => $address->getStreet(),
                'city'      => $address->getCity(),
                'postcode'  => $address->getPostcode(),
                'country'   => $address->getCountryId()
            ]
        );
    }

    /**
     * Method to get title
     *
     * @return mixed
     */
    public function getTitle()
    {
        return $this->_scopeConfig->getValue(
            'yehhpay/advanced/title',
            $this->_scopeStore
        );
    }

    /**
     * Method to redirect for yehhpay transaction create controller,
     * if payment method yehhpay is choosed at checkout form
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return $this->_urlBuilder->getUrl('yehhpay/transaction/create');
    }
}
