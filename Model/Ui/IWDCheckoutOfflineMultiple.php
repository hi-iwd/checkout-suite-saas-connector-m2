<?php

namespace IWD\CheckoutConnector\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Gateway\Config\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use IWD\CheckoutConnector\Model\CacheCleanerFlag;

class IWDCheckoutOfflineMultiple implements ConfigProviderInterface
{

    const CODE = 'iwd_checkout_multiple_payment';

    public $code;

    private $config;

    private $scopeConfig;

    private $configWriter;

    private $cacheCleanerFlag;

    /**
     * @param Config $config
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface $configWriter
     * @param CacheCleanerFlag $cacheCleanerFlag
     */

    public function __construct(
        Config $config,
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter,
        CacheCleanerFlag $cacheCleanerFlag
    ) {
        $this->config = $config;
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->cacheCleanerFlag = $cacheCleanerFlag;
    }

    public function getConfig()
    {
        return [
            'payment' => [
                $this->code => [
                    'label' => $this->config->getValue('label'),
                    'description' => $this->config->getValue('description')
                ]
            ]
        ];
    }

    public function getConfigPath($config)
    {
        return 'payment/' . $this->code . '/' . $config;
    }

    public function getConfigData($config)
    {
        return $this->scopeConfig->getValue($this->getConfigPath($config));
    }

    public function getPaymentMethodCode()
    {
        return self::CODE;
    }

    public function updateConfig($configs, $key) {
        foreach($configs as $configCode => $configValue) {
            $this->setConfigData($configCode, $configValue, $key);
        }

        $this->cacheCleanerFlag->addFlag();
    }

    public function setConfigData($config, $value, $key)
    {
        $this->configWriter->save('payment/' . $key . '/' . $config,  $value, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
    }

    public function getTittle($code){
        $this->code = $code;
        return $this->getConfigData('title');
    }

    public function getOrderStatus($code){
        $this->code = $code;
        return $this->getConfigData('order_status');
    }

    public function setCode($code){
        $this->code = $code;
    }

    public function getExtraDetails($code) {
        $this->code = $code;
        return $this->getConfigData('extra_details');
    }

    public function getInstruction($code) {
        $this->code = $code;
        return $this->getConfigData('instruction');
    }
}
