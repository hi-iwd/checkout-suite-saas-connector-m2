<?php

namespace IWD\CheckoutConnector\Helper;

use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * @package IWD\CheckoutConnector\Helper
 */
class Data extends AbstractHelper
{
    const PLATFORM = 'Magento2';
    const IWD_CHECKOUT_PAGE_PATH = 'checkout_page';
    const IWD_CHECKOUT_APP_URL = 'https://checkout.iwdagency.com/';
    const XML_PATH_ENABLE = 'iwd_checkout_connector/general/enable';
    const XML_PATH_INTEGRATION_API_KEY = 'iwd_checkout_connector/general/integration_api_key';
    const XML_PATH_INTEGRATION_API_SECRET = 'iwd_checkout_connector/general/integration_api_secret';
    const COUNTRY_CODE = 'general/country/default';
    const XML_PATH_SUBSCRIPTION_ENABLE = 'iwd_checkout_connector/general/enable_subscription';

    /**
     * @var Config
     */
    private $resourceConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param Config $resourceConfig
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $url
     */
    public function __construct(
        Context $context,
        Config $resourceConfig,
        StoreManagerInterface $storeManager,
        UrlInterface $url
    ) {
        $this->resourceConfig = $resourceConfig;
        $this->storeManager = $storeManager;
        $this->url = $url;

        parent::__construct($context);
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isEnable($storeId = null)
    {
        $apiKey = $this->getIntegrationApiKey($storeId);
        if (!empty($apiKey)) {
            $status = $this->scopeConfig->getValue(
                self::XML_PATH_ENABLE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
            return (bool)$status;
        } else {
            return false;
        }
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function getIntegrationApiKey($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_INTEGRATION_API_KEY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function getIntegrationApiSecret($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_INTEGRATION_API_SECRET,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return string
     */
    public static function getPlatform()
    {
        return self::PLATFORM;
    }

    /**
     * @return string
     */
    public function getCheckoutPageUrl()
    {
        return $this->url->getUrl($this->getCheckoutPagePath());
    }

    /**
     * @return string
     */
    public static function getAppUrl()
    {
        return self::IWD_CHECKOUT_APP_URL;
    }

    /**
     * @return string
     */
    public static function getCheckoutPagePath()
    {
        return self::IWD_CHECKOUT_PAGE_PATH;
    }

    /**
     * @return string
     */
    public function getCheckConnectionAppUrl()
    {
        return $this->getAppUrl() . 'checkout/check-connection';
    }

    /**
     * @return string
     */
    public function getCheckoutAppUrl()
    {
        return $this->getAppUrl() . 'checkout/opc';
    }

    /**
     * @return string
     */
    public function getOrderStatusUpdateUrl()
    {
        return $this->getAppUrl() . 'order/change-status';
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isCurrentlySecure()
    {
        return (bool)$this->storeManager->getStore()->isCurrentlySecure();
    }

    /**
     * Strip Base Url from protocol prefixes and ending slash
     *
     * @return string|string[]|null
     * @throws NoSuchEntityException
     */
    public function getCleanStoreUrl()
    {
        $storeUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_WEB);

        return preg_replace('#^https?://#', '', rtrim($storeUrl,'/'));
    }

    public function getDefaultCountryCode($storeId = null){
        return $this->scopeConfig->getValue(
            self::COUNTRY_CODE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isSubscription($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_SUBSCRIPTION_ENABLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
