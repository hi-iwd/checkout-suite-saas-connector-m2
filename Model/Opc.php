<?php

namespace IWD\CheckoutConnector\Model;

use Exception;
use IWD\CheckoutConnector\Api\OpcInterface;
use IWD\CheckoutConnector\Helper\Locale;
use IWD\CheckoutConnector\Model\Address\Addresses;
use IWD\CheckoutConnector\Model\Address\Country;
use IWD\CheckoutConnector\Model\Address\Regions;
use IWD\CheckoutConnector\Model\Address\ShippingMethods;
use IWD\CheckoutConnector\Model\Cart\CartItems;
use IWD\CheckoutConnector\Model\Cart\CartTotals;
use IWD\CheckoutConnector\Model\Quote\Quote;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class AddressStep
 * @package IWD\CheckoutConnector\Model
 */
class Opc implements OpcInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CartItems
     */
    private $cartItems;

    /**
     * @var CartTotals
     */
    private $cartTotals;

    /**
     * @var Country
     */
    private $country;

    /**
     * @var Regions
     */
    private $regions;

    /**
     * @var AccessValidator
     */
    private $accessValidator;

    /**
     * @var ShippingMethods
     */
    private $shippingMethods;

    /**
     * @var Addresses
     */
    private $address;

    /**
     * @var Quote
     */
    private $quote;

    /**
     * @var ResolverInterface
     */
    private $localeResolver;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var CustomDataProvider
     */
    private $customDataProvider;

    /**
     * @var Locale
     */
    private $localeHelper;

    /**
     * AddressStep constructor.
     *
     * @param CartItems $cartItems
     * @param CartTotals $cartTotals
     * @param Country $country
     * @param Regions $regions
     * @param AccessValidator $accessValidator
     * @param ShippingMethods $shippingMethods
     * @param Addresses $address
     * @param Quote $quote
     * @param StoreManagerInterface $storeManager
     * @param ResolverInterface $localeResolver
     * @param ScopeConfigInterface $scopeConfig
     * @param CustomDataProvider $customDataProvider
     * @param Locale $localeHelper
     */
    public function __construct(
        CartItems $cartItems,
        CartTotals $cartTotals,
        Country $country,
        Regions $regions,
        AccessValidator $accessValidator,
        ShippingMethods $shippingMethods,
        Addresses $address,
        Quote $quote,
        ResolverInterface $localeResolver,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        CustomDataProvider $customDataProvider,
        Locale $localeHelper
    ) {
        $this->cartItems = $cartItems;
        $this->cartTotals = $cartTotals;
        $this->country = $country;
        $this->regions = $regions;
        $this->accessValidator = $accessValidator;
        $this->shippingMethods = $shippingMethods;
        $this->address = $address;
        $this->quote = $quote;
        $this->storeManager = $storeManager;
        $this->localeResolver = $localeResolver;
        $this->scopeConfig = $scopeConfig;
        $this->customDataProvider = $customDataProvider;
        $this->localeHelper = $localeHelper;
    }

    /**
     * @param string $quote_id
     * @param mixed $access_tokens
     * @return mixed[]|string
     */
    public function getData($quote_id, $access_tokens)
    {
        if(!$this->accessValidator->checkAccess($access_tokens)) {
            return 'Permissions Denied!';
        }

        try {
            $response = $this->prepareData($quote_id);
        } catch (Exception $e) {
            $response = [
                'errors'  => true,
                'message' => $e->getMessage()
            ];
        }

        return $response;
    }

    /**
     * @param $quote_id
     * @return mixed
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function prepareData($quote_id)
    {
        $quote = $this->quote->getQuote($quote_id);

        // Set currently selected Currency for Quote. Otherwise Totals will be collected using Base Currency.
        $this->storeManager->getStore($quote->getStoreId())
            ->setCurrentCurrencyCode($quote->getQuoteCurrencyCode());

        // Set currently locale (en_US, de_DE, etc.) based in store ID.
        $this->setStoreLocale($quote->getStoreId());

        // Get Customer Saved Addresses if he/she is logged in.
        if($this->address->isLoggedIn($quote)) {
            $response['saved_addresses'] = $this->address->getSavedCustomerAddresses($quote);

            $customerAddresses = $this->address->getCustomerAddresses($quote);
            
            if ($customerAddresses) {
                if(!empty($customerAddresses['shipping'])){
                    $quote->getShippingAddress()->addData($customerAddresses['shipping']);
                }
                if(!empty($customerAddresses['billing'])){
                    $quote->getBillingAddress()->addData($customerAddresses['billing']);
                }
            }
        }

        // Pass Shipping Methods in response if Quote is not Virtual and if Shipping Address is set.
        if(!$quote->getIsVirtual() && $quote->getShippingAddress()->getCountryId()) {
            $shippingMethods = $this->shippingMethods->getShippingMethods($quote);

            if(!empty($shippingMethods)) {
                $selectedShippingMethod = $this->shippingMethods->getSelectedShippingMethod($quote);

                // If Shipping Method is not selected yet by the customer, select the first available one.
                if(empty($selectedShippingMethod)
                    || !$this->shippingMethods->isSelectedShippingMethodAvailable($shippingMethods, $selectedShippingMethod)) {
                    $selectedShippingMethod = $shippingMethods[0];

                    $quote->getShippingAddress()->setShippingMethod($selectedShippingMethod['method_code'])
                        ->setCollectShippingRates(true);

                    $quote->collectTotals();
                }

                $response['delivery_methods']       = $shippingMethods;
                $response['chosen_delivery_method'] = $selectedShippingMethod;
            }
        }

        $quote->save();

        $available_countries = $this->country->getCountry($quote);
        $all_countries = $this->country->getAllCountry();
        $available_regions = $this->regions->getRegions();
        $all_regions = $this->regions->getAllRegions();

        if ($available_countries !== $all_countries) {
            $response['available_billing_countries'] = $all_countries;
            $response['available_billing_regions'] = $all_regions;
        }

        $response['available_countries'] = $available_countries;
        $response['available_regions']   = $available_regions;
        $response['addresses']           = $this->address->formatAddress($quote);
        $response['cart_items']          = $this->cartItems->getItems($quote);
        $response['cart']                = $this->cartTotals->getTotals($quote);
        $response['custom_data']         = $this->customDataProvider->getData($quote);

        return $response;
    }

    /**
     * @param $storeId
     * @return void
     */
    private function setStoreLocale($storeId)
    {
        $browserLocale = $this->localeHelper->getBrowserLocale();
        $storeLocale   = $this->scopeConfig->getValue('general/locale/code', ScopeInterface::SCOPE_STORE, $storeId);

        foreach ([$browserLocale, $storeLocale] as $locale) {
            if (!$locale) {
                continue;
            }

            try {
                $this->localeResolver->setLocale($locale);

                return;
            } catch (Exception $e) {}
        }
    }
}
