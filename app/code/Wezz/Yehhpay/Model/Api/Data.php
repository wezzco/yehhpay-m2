<?php

namespace Wezz\Yehhpay\Model\Api;

use Magento\Checkout\Model\Cart\CartInterface;
use Zend_Locale as ZendLocale;

/**
 * Class for working with data for Api Yehhpay
 *
 * Class Data
 */
class Data
{
    protected $session;
    protected $countryFactory;
    protected $countryInterface;
    protected $urlInterface;
    protected $quote;
    protected $cart;
    protected $messageManager;
    protected $order;
    protected $scopeConfig;
    protected $scopeStore;
    protected $invoiceService;
    protected $transaction;
    protected $urlBackendInterface;
    protected $transactionFactory;
    protected $sequence;

    /**
     * @param \Magento\Checkout\Model\Session $session
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInterface
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Checkout\Model\Session $session,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInterface,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Checkout\Model\Cart\Interceptor $cart,
        \Magento\Sales\Model\Order $order,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Backend\Model\UrlInterface $backendUrlInterface,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\SalesSequence\Model\Sequence $sequence,
        \Magento\SalesSequence\Model\Manager $sequenceManager
    ) {
    
        $this->session = $session;
        $this->countryFactory = $countryFactory;
        $this->countryInterface = $countryInterface;
        $this->urlInterface = $urlInterface;
        $this->quote = $quote;
        $this->cart = $cart;
        $this->order = $order;
        $this->messageManager = $messageManager;
        $this->scopeConfig = $scopeConfig;
        $this->scopeStore = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->orderRepository = $orderRepository;
        $this->backendUrlInterface = $backendUrlInterface;
        $this->transactionFactory = $transactionFactory;
        $this->quoteRepository = $quoteRepository;
        $this->sequence = $sequence;
        $this->sequenceManager = $sequenceManager;
    }

    /**
     * Method to create transaction in Yehhpay Api
     */
    public function prepareDataForTransaction()
    {
        /**
         * Get current order object
         */
        $order = $this->session->getLastRealOrder();
        $orderId = $order->getRealOrderId();

        if (empty($order->getShippingAddress())) {
            return [];
        }

        /**
         * Prepare address and invoice data array for consumer struct of future transaction
         */
        $addressObject = $order->getShippingAddress();

        $addressData = [
            'postcode' => $addressObject->getPostcode(),
            'houseNumber' => '',
            'houseNumberAddition' => '',
            'street' => implode(', ', $addressObject->getStreet()),
            'city' => $addressObject->getCity(),
            'countryCode' => $this->getIso3CountryCode($addressObject->getCountryId())
        ];

        /**
         * Use Zend Locale to definite exact language from configuration locale
         * $languageCode is actual language code from local
         */
        $zendLocale = new ZendLocale();
        $languageCode = $zendLocale->getLanguage();

        $firstname = $addressObject->getFirstname() ? $addressObject->getFirstname() : $order->getCustomerFirstname();
        $lastname = $addressObject->getLastname() ? $addressObject->getLastname() : $order->getCustomerLastname();
        $email = $addressObject->getEmail() ? $addressObject->getEmail() : $order->getCustomerEmail();

        /**
         * Prepare data array for consumer struct
         *
         * According to API documentation,
         * address struct and invoiceAddress struct
         * must be equal, so it will be use one prepared array $addressData
         * for both keys.
         */
        $transactionData['consumer'] = [
            'language' => $languageCode,
            'firstName' => $firstname,
            'lastName' => $lastname,
            'phoneNumber' => $addressObject->getTelephone(),
            'emailAddress' => $email,
            'address' => $addressData,
            'invoiceAddress' => $addressData,
            'dateOfBirth' => null,
            'ipAddress' => $this->getRemoteAddr(),
            'trustScore' => null
        ];

        $date = new \DateTime();
        $date->modify('+1 day');

        /**
         * Prepare data array for order struct
         */
        $transactionData['order'] = [
            'identifier' => $orderId,
            'invoiceDate' => $date->format('Y-m-d H:i:s'),
            'deliveryDate' => null,
            'redirectUrl' => $this->urlInterface->getRouteUrl(
                'yehhpay/transaction/check',
                ['order' => $orderId]
            ),
            'notificationUrl' => $this->urlInterface->getRouteUrl(
                'yehhpay/transaction/notify',
                ['order' => $orderId]
            ),
        ];

        /**
         * Prepare data array for order items struct inside order struct
         */
        $orderItems = $order->getItemsCollection([], true);

        foreach ($orderItems as $item) {
            $price = $item->getPriceInclTax() ? $item->getPriceInclTax() : 0;

            $transactionData['order']['products'][] = [
                'price' => $price,
                'quantity' => $item->getQtyOrdered(),
                'identifier' => $item->getProductId(),
                'description' => $item->getName()
            ];
        }

        if ($order->getShippingInclTax() > 0) {
            $shippingVirtualItem = [
                'price' => $order->getShippingInclTax(),
                'quantity' => '1',
                'identifier' => 'Shipping',
                'description' => 'Shipping Cost'
            ];

            if (isset($transactionData['order']['products'])) {
                $transactionData['order']['products'][] = $shippingVirtualItem;
            }
        }

        if ($order->getDiscountAmount() != 0) {
            $discountVirtualItem = [
                'price' => $order->getDiscountAmount(),
                'quantity' => '1',
                'identifier' => 'Discount',
                'description' => 'Discount Amount'
            ];

            if (isset($transactionData['order']['products'])) {
                $transactionData['order']['products'][] = $discountVirtualItem;
            }
        }

        return $transactionData;
    }

    /**
     * Method to save transactionId at db
     *
     * @param $transactionId
     */
    public function saveTransactionId($transactionId)
    {
        /**
         * Get current order object by order_id
         */
        $order = $this->session->getLastRealOrder();
        $payment = $order->getPayment();
        $payment->setYehhpayTransactionId($transactionId);
        $payment->save();
    }

    /**
     * Method to set order status
     * according to yehhpay transaction status responses
     * @param $resumeData
     * @param $orderId
     *
     * @return boolean
     */
    public function saveResumeDate($resumeData, $orderId)
    {
        if (!$orderId) {
            return false;
        }

        $order = $this->getOrderById($orderId);

        if (!$order) {
            return false;
        }

        $payment = $order->getPayment();

        if ($payment) {
            $payment->setYehhpayTransactionDate($resumeData);
            $payment->save();
        }
    }

    /**
     * Method to set order status
     * according to yehhpay transaction status responses
     *
     * @param $success
     */
    public function setOrderStatus($success, $orderId = false)
    {
        if ($success) {
           // $status = $this->getPaymentSuccessStatus();
        } else {
            $status = $this->getPaymentFailedStatus();

            if (!$orderId) {
                $order = $this->session->getLastRealOrder();
            } else {
                $order = $this->order->loadByIncrementId($orderId);
            }

            $order->setStatus($status);
            $order->save();
        }
    }

    /**
     * Method to get transaction id by order id from
     * sales_flat_order_payment table
     *
     * @param int $orderId
     * @return int | boolean
     */
    public function getTransactionIdByOrderId($orderId)
    {
        /**
         * Make query to 'sales_flat_order_payment' to get transaction id from data collection (db_field = 'yehhpay_transaction_id')
         */
        $collection = $this->order->getCollection()->addFieldToFilter('main_table.entity_id', $orderId);
        $collection->getSelect()->join(
            ['payment' => 'sales_order_payment'],
            'payment.parent_id = main_table.entity_id',
            [
                'payment_method' => 'payment.method',
                'yehhpay_transaction_id' => 'payment.yehhpay_transaction_id'
            ]
        );
        $collectionData = $collection->getFirstItem()->getData();
        $transactionId =  isset($collectionData['yehhpay_transaction_id']) ? $collectionData['yehhpay_transaction_id'] : false;

        if (!$transactionId) {
            return false;
        }

        return $transactionId;
    }

    /**
     * Method to create invoice
     *
     * @param $orderId
     * @param $transactionId
     */
    public function createInvoice($orderId, $transactionId)
    {
        $order = $this->order->loadByIncrementId($orderId);

        $invoices = $this->order->getInvoiceCollection();

        if (empty($invoices->getData())) {
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->capture();
            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_ONLINE);
            $invoice->setState(\Magento\Sales\Model\Order\Invoice::STATE_PAID);
            $invoice->setTransactionId($transactionId);
            $invoice->setSendEmail(true);
            $invoice->register();
            $invoice->save();

            $this->invoiceService->notify($invoice->getId());

            $transactionSave = $this->transactionFactory->create()->addObject($invoice)->addObject($invoice->getOrder());
            $transactionSave->save();
        }

        if ($order->getState() != $this->getPaymentSuccessStatus()) {
            $order->setStatus($this->getPaymentSuccessStatus());
            $order->save();
        }
    }

    /**
     * Method to get transaction by increment id
     *
     * @param $orderId
     * @return bool|int
     */
    public function getTransactionIdByIncrementId($orderId)
    {
        $order = $this->order->loadByIncrementId($orderId);

        if (!$order->getId()) {
            return false;
        }

        return $this->getTransactionIdByOrderId($order->getId());
    }

    /**
     * Method to return old quote if yehhpay transaction was created with errors
     */
    public function returnOldQuote()
    {
        $order = $this->session->getLastRealOrder();
        $quote = $this->quoteRepository->get($order->getQuoteId());
        $this->session->replaceQuote($quote);

        $nextValue = $this->sequenceManager->getSequence(
            $order->getEntityType(),
            $order->getStore()->getGroup()->getDefaultStoreId()
        )->getNextValue();

        $currentQuote = $this->quoteRepository->get($this->session->getQuoteId());
        $currentQuote->setIsActive(1);
        $currentQuote->setReservedOrderId($nextValue);
        $this->quoteRepository->save($currentQuote);

        $this->cart->setQuote($currentQuote);
        $this->cart->save();

        $this->messageManager->addWarningMessage(__("Yehhpay transaction is not completed and approved. Please try to re-order."));
    }

    /**
     * Method to get order by order id
     *
     * @param $orderId
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrderById($orderId)
    {
        return $this->orderRepository->get($orderId);
    }

    /**
     * Method to prepare calendar dates
     */
    public function getCalendarDates()
    {
        $zendData = new \Zend_Date();

        $currentDate = $zendData->getDate();

        $data['start'] = $currentDate->addDay('1')->getIso();
        $data['end'] = $currentDate->addDay('7')->getIso();

        return $data;
    }

    /**
     * Get url
     *
     * @param $routePath
     * @param $params
     * @return string
     */
    public function getUrl($routePath, $params)
    {
        if ($routePath && is_array($params)) {
            return $this->backendUrlInterface->getRouteUrl($routePath, $params);
        } else {
            return '';
        }
    }

    /**
     * Method to get remote addr value
     *
     * @return mixed
     */
    protected function getRemoteAddr()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $obj = $om->get('Magento\Framework\HTTP\PhpEnvironment\RemoteAddress');
        return $obj->getRemoteAddress();
    }

    /**
     * Method to get Iso3 country code
     *
     * @param $countryId
     * @return bool|string
     */
    protected function getIso3CountryCode($countryId)
    {
        if (!$countryId) {
            return false;
        }

        $countryInfo = $this->countryInterface->getCountryInfo($countryId);

        if ($countryInfo) {
            $countryCode = $countryInfo->getThreeLetterAbbreviation();
        } else {
            $countryCode = $countryId;
        }

        return $countryCode;
    }

    /**
     * Method to get payment success status
     *
     * @return mixed
     */
    private function getPaymentSuccessStatus()
    {
        return $this->scopeConfig->getValue(
            'yehhpay/advanced/payment_success_status',
            $this->scopeStore
        );
    }

    /**
     * Method to get payment failure status
     */
    private function getPaymentFailedStatus()
    {
        return $this->scopeConfig->getValue(
            'yehhpay/advanced/payment_failed_status',
            $this->scopeStore
        );
    }

    /**
     * Method to get order id by transaction id from
     * sales_flat_order_payment table
     *
     * @param int $transactionId
     * @return int | boolean
     */
    public function getOrderByTransactionId($transactionId)
    {
        /**
         * Make query to 'sales_flat_order_payment' to get order id from data collection
         */
        $collection = $this->order->getCollection();

        $collection->getSelect()->join(
            ['payment' => 'sales_order_payment'],
            'payment.parent_id = main_table.entity_id',
            [
                'payment_method' => 'payment.method',
                'yehhpay_transaction_id' => 'payment.yehhpay_transaction_id'
            ]
        );
        $collection->addFieldToFilter('payment.yehhpay_transaction_id', $transactionId);

        $collectionData = $collection->getFirstItem()->getData();


        $orderId = isset($collectionData['increment_id']) ? $collectionData['increment_id'] : false;

        return $orderId;
    }
}
