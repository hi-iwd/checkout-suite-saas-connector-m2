<?php

namespace IWD\CheckoutConnector\Helper;

use IWD\CheckoutConnector\Model\Address\Addresses;
use IWD\CheckoutConnector\Model\Address\ShippingMethods;
use IWD\CheckoutConnector\Model\Cart\CartTotals;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Integration\Model\Oauth\TokenFactory;

/**
 * Class Data
 *
 * @package IWD\CheckoutConnector\Helper
 */
class Data extends AbstractHelper
{
    const PLATFORM = 'Magento2';
	const IWD_CHECKOUT_IFRAME_ID = 'iwd_checkout_iframe';
    const IWD_CHECKOUT_PAGE_PATH = 'checkout_page';
    const IWD_CHECKOUT_APP_URL = 'https://checkout.iwdagency.com/';
    const XML_PATH_ENABLE = 'iwd_checkout_connector/general/enable';
    const XML_PATH_INTEGRATION_API_KEY = 'iwd_checkout_connector/general/integration_api_key';
    const XML_PATH_INTEGRATION_API_SECRET = 'iwd_checkout_connector/general/integration_api_secret';
    const COUNTRY_CODE = 'general/country/default';
    const XML_PATH_SUBSCRIPTION_ENABLE = 'iwd_checkout_connector/general/enable_subscription';
    const XML_PATH_NOTIFY_ENABLE = 'iwd_checkout_connector/notify/enable_notification';
    const XML_PATH_NOTIFY_EMAIL = 'iwd_checkout_connector/notify/template';
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
	 * @var CartTotals
	 */
	private $cartTotals;

	/**
	 * @var ShippingMethods
	 */
	private $shippingMethods;

	/**
	 * @var Addresses
	 */
	private $address;

	/**
	 * @var CustomerSession
	 */
	private $customerSession;

	/**
	 * @var CheckoutSession
	 */
	private $checkoutSession;

	/**
	 * @var QuoteIdMaskFactory
	 */
	private $quoteIdMaskFactory;

	/**
	 * @var TokenFactory
	 */
	private $tokenModelFactory;

	/**
	 * @var Http
	 */
	protected $httpRequest;

	/**
	 * @var CheckoutHelper
	 */
	private $checkoutHelper;

	/**
	 * @param Context               $context
	 * @param Config                $resourceConfig
	 * @param StoreManagerInterface $storeManager
	 * @param UrlInterface          $url
	 * @param CartTotals            $cartTotals
	 * @param ShippingMethods       $shippingMethods
	 * @param Addresses             $address
	 * @param CustomerSession       $customerSession
	 * @param CheckoutSession       $checkoutSession
	 * @param QuoteIdMaskFactory    $quoteIdMaskFactory
	 * @param TokenFactory          $tokenModelFactory
	 * @param Http                  $httpRequest
	 * @param CheckoutHelper        $checkoutHelper
	 */
	public function __construct(
		Context $context,
		Config $resourceConfig,
		StoreManagerInterface $storeManager,
		UrlInterface $url,
		CartTotals $cartTotals,
		ShippingMethods $shippingMethods,
		Addresses $address,
		CustomerSession $customerSession,
		CheckoutSession $checkoutSession,
		QuoteIdMaskFactory $quoteIdMaskFactory,
		TokenFactory $tokenModelFactory,
		Http $httpRequest,
		CheckoutHelper $checkoutHelper
	) {
		$this->resourceConfig     = $resourceConfig;
		$this->storeManager       = $storeManager;
		$this->url                = $url;
		$this->cartTotals         = $cartTotals;
		$this->shippingMethods    = $shippingMethods;
		$this->address            = $address;
		$this->customerSession    = $customerSession;
		$this->checkoutSession    = $checkoutSession;
		$this->quoteIdMaskFactory = $quoteIdMaskFactory;
		$this->tokenModelFactory  = $tokenModelFactory;
		$this->httpRequest        = $httpRequest;
		$this->checkoutHelper     = $checkoutHelper;

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
	public static function getCheckoutIframeId()
	{
		return self::IWD_CHECKOUT_IFRAME_ID;
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
    public function getOrderUpdateUrl()
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

	/**
	 * @param $storeId
	 *
	 * @return mixed
	 */
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

	/**
	 * @param  Quote  $quote
	 *
	 * @return array
	 */
	public function getFrameParams(Quote $quote)
	{
		$addresses = $this->address->formatAddress($quote);

		$this->shippingMethods->getShippingMethods($quote);

		return [
			'lazy'             => true,
			'api_key'          => $this->getIntegrationApiKey(),
			'quote_id'         => $quote->getId(),
			'shipping_methods' => count($this->shippingMethods->getAvailableShippingMethods()),
			'cart'             => $this->cartTotals->getTotals($quote, false),
			'cart_items'       => count($quote->getAllVisibleItems()),
			'address'          => [
				'saved'             => (bool) $this->address->getSavedCustomerAddresses($quote),
				'shipping'          => (bool) $addresses['shipping']['address'],
				'billing'           => (bool) $addresses['billing']['address'],
				'ship_bill_to_diff' => $addresses['ship_bill_to_different_address'],
			],
		];
	}

    /**
     * @return bool
     */
    public function isNotifyCustomer($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_NOTIFY_ENABLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return bool
     */
    public function notifyCustomerTemplate($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_NOTIFY_EMAIL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

	/**
	 * @return mixed
	 */
	public function getCustomerToken()
	{
		if ($this->customerSession->isLoggedIn()) {
			$customerId    = $this->customerSession->getCustomer()->getId();
			$customerToken = $this->tokenModelFactory->create();

			return $customerToken->createCustomerToken($customerId)->getToken();
		}

		return 'empty';
	}

	/**
	 * @return mixed
	 */
	public function getQuoteId()
	{
		return $this->getQuote()->getId();
	}

	/**
	 * @return Quote
	 */
	public function getQuote()
	{
		return $this->checkoutSession->getQuote();
	}

	/**
	 * @return mixed
	 */
	public function isQuoteVirtual()
	{
		return $this->getQuote()->isVirtual();
	}

	/**
	 * @return mixed
	 */
	public function isCheckoutPage()
	{
		return $this->httpRequest->getFullActionName() === self::IWD_CHECKOUT_PAGE_PATH.'_index_index';
	}

	/**
	 * @return string
	 * @throws NoSuchEntityException
	 */
	public function getStoreCode()
	{
		return $this->storeManager->getStore()->getCode();
	}

	/**
	 * @return mixed
	 */
	public function getMaskedQuoteId()
	{
		$quoteId = $this->getQuoteId();
		$quoteIdMask = $this->quoteIdMaskFactory->create()->load($quoteId, 'quote_id');

		return $quoteIdMask->getMaskedId();
	}

	/**
	 * @return string
	 */
	public function getGrandTotalAmount()
	{
		if (!$this->getQuote()->getBaseGrandTotal()) return 0;

		return number_format($this->getQuote()->getBaseGrandTotal(),2,'.', '');
	}

	/**
	 * @return string
	 */
	public function getCurrencyCode()
	{
		return $this->getQuote()->getBaseCurrencyCode();
	}

	/**
	 * @return string
	 */
	public function getActionSuccess()
	{
		return $this->url->getUrl('checkout_page/index/success');
	}

	/**
	 * @return bool
	 */
	public function isCustomerLoggedIn()
	{
		return (bool)$this->customerSession->isLoggedIn();
	}

	/**
	 * @return string
	 * @throws NoSuchEntityException
	 */
	public function getMerchantName()
	{
		return $this->storeManager->getStore()->getName();
	}

	/**
	 * Check if guest checkout is allowed
	 *
	 * @return bool
	 */
	public function isCheckoutAllowed()
	{
		return $this->customerSession->isLoggedIn() || $this->checkoutHelper->isAllowedGuestCheckout($this->getQuote());
	}
}
