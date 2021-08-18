<?php

namespace IWD\CheckoutConnector\Plugin\Checkout\CustomerData;

use Magento\Checkout\CustomerData\Cart as MagentoCart;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutPayConfigProvider;
use IWD\CheckoutConnector\Helper\Data as IWDHelper;
use IWD\CheckoutConnector\Block\Frame as IWDFrameBlock;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Class Cart
 *
 * @package IWD\CheckoutConnector\Plugin\Checkout
 */
class Cart
{
    private $applePayMerchantIdentifier = "merchant.com.spreedly";
    /**
     * @var IWDCheckoutPayConfigProvider
     */
    private $configProvider;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var
     */
    private $IWDFrameBlock;
    /**
     * @var \IWD\CheckoutConnector\Helper\Data
     */
    private $helper;

    /**
     * Cart constructor.
     *
     * @param IWDCheckoutPayConfigProvider $configProvider
     */
    public function __construct(
        IWDCheckoutPayConfigProvider $configProvider,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        IWDHelper $helper,
        IWDFrameBlock $IWDFrameBlock
    ) {
        $this->configProvider = $configProvider;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->helper = $helper;
        $this->IWDFrameBlock = $IWDFrameBlock;
    }

    /**
     * @param MagentoCart $subject
     * @param array $result
     * @return array
     * @throws LocalizedException
     */
    public function afterGetSectionData(MagentoCart $subject, array $result)
    {
        $config = $this->configProvider;

        $result['paypal_credit_msg_config'] = [
            'container_id'       => $config->getGeneratedContainerId(),
            'grand_total_amount' => $config->getGrandTotalAmount(),
            'logo_type'          => $config->getConfigData('credit_msg_logo_type'),
            'logo_position'      => $config->getConfigData('credit_msg_logo_position'),
            'text_color'         => $config->getConfigData('credit_msg_text_color'),
        ];

        $result['apple_pay'] = [
            'quote_id' => $this->checkoutSession->getQuote()->getId(),
            'payment_methods' => $config->getConfigData('apple_pay_minicart'),
            'merchant_id' => $this->applePayMerchantIdentifier,
            'currency_code' => $this->checkoutSession->getQuote()->getBaseCurrencyCode(),
            'country_code' => $this->helper->getDefaultCountryCode(),
            'api_key' => $this->helper->getIntegrationApiKey(),
            'customer_id' => $this->customerSession->isLoggedIn() ? $this->customerSession->getCustomer()->getId() : NULL,
            'customer_email' => $this->customerSession->isLoggedIn() ? $this->customerSession->getCustomer()->getEmail() : NULL,
            'customer_token' => $this->IWDFrameBlock->getCustomerToken(),
            'iwd_checkout_app_url' => $this->helper::IWD_CHECKOUT_APP_URL,
        ];

        return $result;
    }
}
