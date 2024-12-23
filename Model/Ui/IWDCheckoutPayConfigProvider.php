<?php

namespace IWD\CheckoutConnector\Model\Ui;

use IWD\CheckoutConnector\Helper\Data as Helper;
use IWD\CheckoutConnector\Model\Address\Country;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Math\Random;
use Magento\Payment\Gateway\Config\Config;
use Magento\Quote\Model\Quote;

/**
 * Class IWDCheckoutPayConfigProvider
 */
class IWDCheckoutPayConfigProvider implements ConfigProviderInterface
{
	const CODE = 'iwd_checkout_pay';
	const CONTAINER = 'iwd-checkout-pay-container';

	/**
	 * @var Random
	 */
	private $random;

	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @var CheckoutSession
	 */
	private $helper;

	/**
	 * @var ScopeConfigInterface
	 */
	protected $scopeConfig;

	/**
	 * @var WriterInterface
	 */
	protected $configWriter;

	/**
	 * @var Country
	 */
	protected $country;

	/**
	 * @param Config               $config
	 * @param Random               $random
	 * @param Helper               $helper
	 * @param ScopeConfigInterface $scopeConfig
	 * @param WriterInterface      $configWriter
	 * @param Country              $country
	 */
	public function __construct(
		Config $config,
		Random $random,
		Helper $helper,
		ScopeConfigInterface $scopeConfig,
		WriterInterface $configWriter,
		Country $country,
	) {
		$this->config       = $config;
		$this->random       = $random;
		$this->helper       = $helper;
		$this->scopeConfig  = $scopeConfig;
		$this->configWriter = $configWriter;
		$this->country      = $country;
	}

	/**
	 * Retrieve assoc array of checkout configuration
	 *
	 * @return array
	 */
	public function getConfig()
	{
		return [
			'payment' => [
				self::CODE => [
					'label'       => $this->config->getValue('label'),
					'description' => $this->config->getValue('description'),
				],
			],
		];
	}

	/**
	 * @param $containerId
	 *
	 * @return array
	 * @throws NoSuchEntityException
	 */
	public function getButtonConfig($containerId)
	{
		return [
			'containerId'         => $containerId,
			'checkoutIframeId'    => $this->helper->getCheckoutIframeId(),
			'checkoutPageUrl'     => $this->helper->getCheckoutPageUrl(),
			'successActionUrl'    => $this->helper->getActionSuccess(),
			'dominateApiKey'      => $this->helper->getIntegrationApiKey(),
			'dominateAppUrl'      => $this->helper->getAppUrl(),
			'customerToken'       => $this->helper->getCustomerToken(),
			'quoteId'             => $this->helper->getQuoteId(),
			'maskedQuoteId'       => $this->helper->getMaskedQuoteId(),
			'isVirtual'           => $this->helper->isQuoteVirtual(),
			'displayName'         => $this->helper->getMerchantName(),
			'isLoggedIn'          => $this->helper->isCustomerLoggedIn(),
			'isCheckoutAllowed'   => $this->helper->isCheckoutAllowed(),
			'isCheckoutPage'      => $this->helper->isCheckoutPage(),
			'storeCode'           => $this->helper->getStoreCode(),
			'currencyCode'        => $this->helper->getCurrencyCode(),
			'grandTotalAmount'    => $this->helper->getGrandTotalAmount(),
			'btnShape'            => $this->getConfigData('btn_shape'),
			'btnColor'            => $this->getConfigData('btn_color'),
			'paypalStatus'        => (bool) $this->getConfigData('status'),
			'paypalEnvironment'   => $this->getConfigData('paypal_environment'),
			'creditStatus'        => (bool) $this->getConfigData('paypal_credit_status'),
			'venmoStatus'         => (bool) $this->getConfigData('paypal_venmo_status'),
			'applepayStatus'      => (bool) $this->getConfigData('paypal_applepay_status'),
			'googlepayStatus'     => (bool) $this->getConfigData('paypal_googlepay_status'),
			'allowedCountryCodes' => $this->getAllowedCountryCodes(),
		];
	}

	/**
	 * @return array
	 */
	public function getPayPalBannerConfig()
	{
		return $this->getConfigData('paypal_credit_msg_configurator_data')
			? json_decode($this->getConfigData('paypal_credit_msg_configurator_data')) : null;
	}

	/**
	 * @param $configs
	 */
	public function updateConfig($configs)
	{
		foreach ($configs as $configCode => $configValue) {
			$this->setConfigData($configCode, $configValue);
		}
	}

	/**
	 * @return string
	 */
	public function getPaymentMethodCode()
	{
		return self::CODE;
	}

	/**
	 * @param $config
	 *
	 * @return string
	 */
	public function getConfigPath($config)
	{
		return 'payment/'.self::CODE.'/'.$config;
	}

	/**
	 * @param $config
	 *
	 * @return string
	 */
	public function getConfigData($config)
	{
		return $this->scopeConfig->getValue($this->getConfigPath($config));
	}

	/**
	 * @param $config
	 * @param $value
	 */
	public function setConfigData($config, $value)
	{
		$this->configWriter->save($this->getConfigPath($config), $value, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
	}

	/**
	 * @return string
	 * @throws LocalizedException
	 */
	public function getGeneratedContainerId()
	{
		return self::CONTAINER.$this->random->getRandomNumber();
	}

	/**
	 * @return string
	 */
	public function getCurrencyCode()
	{
		return $this->helper->getCurrencyCode();
	}

	/**
	 * @return string
	 */
	public function getGrandTotalAmount()
	{
		return $this->helper->getGrandTotalAmount();
	}

	/**
	 * @return Quote
	 */
	public function getQuote()
	{
		return $this->helper->getQuote();
	}

	/**
	 * @return string
	 */
	public function getTittle()
	{
		return $this->getConfigData('title');
	}

	/**
	 * @return string
	 */
	public function getOrderStatus()
	{
		return $this->getConfigData('order_status');
	}

	/**
	 * Retrieves the allowed country codes for the current quote.
	 *
	 * @return array An array of allowed country codes.
	 */
	private function getAllowedCountryCodes()
	{
		$allowedCountryCodes = [];

		foreach ($this->country->getCountry($this->helper->getQuote()) as $country ){
			$allowedCountryCodes[] = $country['value'];
		}

		return $allowedCountryCodes;
	}
}
