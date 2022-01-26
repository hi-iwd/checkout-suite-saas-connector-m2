<?php

namespace IWD\CheckoutConnector\Block\Shortcut;

use IWD\CheckoutConnector\Block\Frame as IWDFrameBlock;
use IWD\CheckoutConnector\Helper\Data as Helper;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutPayConfigProvider;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Block\ShortcutInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use IWD\CheckoutConnector\Model\Address\Country;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Button
 *
 * @package IWD\CheckoutConnector\Block\Shortcut
 */
class GooglePayButton extends Template implements ShortcutInterface
{
    const ALIAS_ELEMENT_INDEX = 'alias';

    /**
     * @var Session
     */
    private $session;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var IWDCheckoutPayConfigProvider
     */
    private $configProvider;

    /**
     * @var JsonHelper
     */
    private $jsonHelper;
    /**
     * @var Country
     */
    private $country;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    public $_storeManager;

    const COUNTRY_CODE_PATH = 'general/country/default';
    /**
     * @var IWDFrameBlock
     */
    private $IWDFrameBlock;

    /**
     * GooglePayButton constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param Context $context
     * @param Session $session
     * @param Helper $helper
     * @param IWDCheckoutPayConfigProvider $configProvider
     * @param JsonHelper $jsonHelper
     * @param StoreManagerInterface $storeManager
     * @param Country $country
     * @param IWDFrameBlock $IWDFrameBlock
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Context $context,
        Session $session,
        Helper $helper,
        IWDCheckoutPayConfigProvider $configProvider,
        JsonHelper $jsonHelper,
        StoreManagerInterface $storeManager,
        Country $country,
        IWDFrameBlock $IWDFrameBlock,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->scopeConfig = $scopeConfig;
        $this->session = $session;
        $this->helper = $helper;
        $this->configProvider = $configProvider;
        $this->jsonHelper = $jsonHelper;
        $this->_storeManager = $storeManager;
        $this->country = $country;
        $this->IWDFrameBlock = $IWDFrameBlock;
    }

    public function getAlias()
    {
        return $this->getData(self::ALIAS_ELEMENT_INDEX);
    }

    protected function _toHtml()
    {
        if (!$this->helper->isEnable() || empty($this->configProvider->getConfigData('google_pay_minicart'))) {
            return '';
        }

        return parent::_toHtml();
    }
    public function getConfigData()
    {
        $gPayConfig = $this->configProvider->getConfigData('google_pay_minicart');

        if('null' == $gPayConfig || '[]' == $gPayConfig || empty($gPayConfig)){
            return null;
        }

        $buttons = [];
        foreach (json_decode($gPayConfig) as $item => $value){
            $buttons[] = $item;
        }

        $config = [
            "button" => $buttons,
            "api_key" => $this->helper->getIntegrationApiKey(),
            "quote_id" => $this->session->getQuote()->getId(),
            "app_url" => $this->helper->getAppUrl().'spreedly/google-pay',
            "base_url" => $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB),
            'virtual' => $this->session->getQuote()->isVirtual(),
            'customer_token' => $this->IWDFrameBlock->getCustomerToken(),
        ];

        $allowedCountryCodes = [];
        foreach ($this->country->getCountry()as $country ){
            $allowedCountryCodes[] = $country['value'];
        }

        $merchInfo = json_decode($this->configProvider->getConfigData('google_pay_info'));

        $gpay_data = [
            'transactionInfo' => [
                "displayItems" => [
                    [

                        "label" => "Subtotal",
                        "type" => "SUBTOTAL",
                        "price" => number_format($this->session->getQuote()->getBaseGrandTotal(),2,'.',''),
                    ],
                    [

                        "label" => "Tax",
                        "type" => "TAX",
                        "price" => number_format($this->session->getQuote()->getTaxAmount(),2,'.',''),
                    ],
                    [
                        "type" => "LINE_ITEM",
                        "label" => "Shipping cost",
                        "price" =>  number_format($this->session->getQuote()->getShippingAmount(),2,'.',''),
                        "status" => "FINAL"
                    ],
                ],
                'currencyCode' => $this->session->getQuote()->getBaseCurrencyCode(),
                'totalPriceStatus' => "FINAL",
                'totalPrice' => number_format($this->session->getQuote()->getBaseGrandTotal(),2,'.',''),
                'totalPriceLabel' => "Total",
            ],
            'allowedCountryCodes' => $allowedCountryCodes,
            'merchantInfo' => [
                'merchantName' => $merchInfo->merchantName,
                'merchantId' => $merchInfo->merchantId,
                'environment' => 'PRODUCTION'
            ]
        ];

        $data = [
            'config'    => $config,
            'gpay_data' => $gpay_data

        ];

        return $data;
    }
}
