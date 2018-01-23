<?php

namespace Wezz\Yehhpay\Model\Api;

use Braintree\Exception;
use Zend_XmlRpc_Client as XmlRpc;
use Zend_XmlRpc_HttpException as Zend_XmlRpc_HttpException;
use Zend_XmlRpc_FaultException as Zend_XmlRpc_FaultException;

/**
 * Class for working with data for Api Yehhpay
 *
 * Class Client
 */
class Client
{
    protected $scopeConfig;
    protected $scopeStore;
    protected $xmlRpcClient;

    /**
     **
     * Test application endpoint
     */
    const TEST_APPLICATION_ENDPOINT = 'https://api-test.yehhpay.nl/xmlrpc/merchant';

    /**
     **
     * Live application endpoint
     */
    const LIVE_APPLICATION_ENDPOINT = 'https://api.yehhpay.nl/xmlrpc/merchant';

    /**
     * Client constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
    
        $this->scopeConfig = $scopeConfig;
        $this->scopeStore = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
    }

    /**
     * Method for call api
     *
     * @param $method - api method
     * @param $data - data structure for api
     * @return mixed
     */
    public function callApi($method, $data, $create = false)
    {
        if ($create) {
            $apiData['applicationKey'] = $this->getApplicationKey();
            $apiData['applicationSecret'] = $this->getApplicationSecret();
            $apiData['serviceIdentifier'] = $this->getServiceIdentifier();

            foreach ($data as $key => $dataItem) {
                $apiData[$key] = $dataItem;
            }
        } else {
            $apiData[] = $this->getApplicationKey();
            $apiData[] = $this->getApplicationSecret();

            if (is_array($data)) {
                foreach ($data as $dataItem) {
                    $apiData[] = $dataItem;
                }
            } else {
                $apiData[] = $data;
            }
        }

        try {
            $connection = new XmlRpc($this->getApplicationEndPoint());
            $result = $connection->call($method, $apiData);

            return $result;
        } catch (Zend_XmlRpc_FaultException $e) {
            return false;
        } catch (Zend_XmlRpc_HttpException $e) {
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get application key
     *
     * @return mixed
     */
    private function getApplicationKey()
    {
        return $this->scopeConfig->getValue(
            'yehhpay/yehhpay/application_key',
            $this->scopeStore
        );
    }

    /**
     * Get application secret
     *
     * @return mixed
     */
    private function getApplicationSecret()
    {
        return $this->scopeConfig->getValue(
            'yehhpay/yehhpay/application_secret',
            $this->scopeStore
        );
    }

    /**
     * Get service identifier
     *
     * @return mixed
     */
    private function getServiceIdentifier()
    {
        return $this->scopeConfig->getValue(
            'yehhpay/yehhpay/service_identifier',
            $this->scopeStore
        );
    }

    /**
     * Get application mode
     *
     * @return mixed
     */
    private function getApplicationMode()
    {
        return $this->scopeConfig->getValue(
            'yehhpay/yehhpay/payment_mode',
            $this->scopeStore
        );
    }

    /**
     * Get application endpoint
     *
     * @return string
     */
    private function getApplicationEndPoint()
    {
        if ($this->getApplicationMode()) {
            return self::LIVE_APPLICATION_ENDPOINT;
        } else {
            return self::TEST_APPLICATION_ENDPOINT;
        }
    }
}
