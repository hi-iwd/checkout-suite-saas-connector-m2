<?php

namespace IWD\CheckoutConnector\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Gateway\Config\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;

/**
 * Class IWDCheckoutOfflineMultiple
 *
 * @package IWD\CheckoutConnector\Model\Ui
 */
class IWDCheckoutOfflineMultiple implements ConfigProviderInterface
{
	/**
	 * Custom Offline Payment Method Code
	 */
	const CODE = 'iwd_checkout_multiple_payment';

	/**
	 * @var
	 */
	public $code;

	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @var ScopeConfigInterface
	 */
	private $scopeConfig;

	/**
	 * @var WriterInterface
	 */
	private $configWriter;

	/**
	 * @param Config               $config
	 * @param ScopeConfigInterface $scopeConfig
	 * @param WriterInterface      $configWriter
	 */

	public function __construct(
		Config $config,
		ScopeConfigInterface $scopeConfig,
		WriterInterface $configWriter,
	) {
		$this->config       = $config;
		$this->scopeConfig  = $scopeConfig;
		$this->configWriter = $configWriter;
	}

	/**
	 * @return \array[][]
	 */
	public function getConfig()
	{
		return [
			'payment' => [
				$this->code => [
					'label'       => $this->config->getValue('label'),
					'description' => $this->config->getValue('description'),
				],
			],
		];
	}

	/**
	 * @param $config
	 *
	 * @return string
	 */
	public function getConfigPath($config)
	{
		return 'payment/'.$this->code.'/'.$config;
	}

	/**
	 * @param $config
	 *
	 * @return mixed
	 */
	public function getConfigData($config)
	{
		return $this->scopeConfig->getValue($this->getConfigPath($config));
	}

	/**
	 * @return string
	 */
	public function getPaymentMethodCode()
	{
		return self::CODE;
	}

	/**
	 * @param $configs
	 * @param $key
	 *
	 * @return void
	 */
	public function updateConfig($configs, $key)
	{
		foreach ($configs as $configCode => $configValue) {
			$this->setConfigData($configCode, $configValue, $key);
		}
	}

	/**
	 * @param $config
	 * @param $value
	 * @param $key
	 *
	 * @return void
	 */
	public function setConfigData($config, $value, $key)
	{
		$this->configWriter->save('payment/'.$key.'/'.$config, $value,
			$scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
	}

	/**
	 * @param $code
	 *
	 * @return mixed
	 */
	public function getTittle($code)
	{
		$this->code = $code;

		return $this->getConfigData('title');
	}

	/**
	 * @param $code
	 *
	 * @return mixed
	 */
	public function getOrderStatus($code)
	{
		$this->code = $code;

		return $this->getConfigData('order_status');
	}

	/**
	 * @param $code
	 *
	 * @return void
	 */
	public function setCode($code)
	{
		$this->code = $code;
	}

	/**
	 * @param $code
	 *
	 * @return mixed
	 */
	public function getExtraDetails($code)
	{
		$this->code = $code;

		return $this->getConfigData('extra_details');
	}

	/**
	 * @param $code
	 *
	 * @return mixed
	 */
	public function getInstruction($code)
	{
		$this->code = $code;

		return $this->getConfigData('instruction');
	}
}