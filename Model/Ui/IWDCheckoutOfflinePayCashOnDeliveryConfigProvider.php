<?php

namespace IWD\CheckoutConnector\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Gateway\Config\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use IWD\CheckoutConnector\Model\CacheCleanerFlag;
use IWD\CheckoutConnector\Model\OfflinePayment\CashOnDelivery;

class IWDCheckoutOfflinePayCashOnDeliveryConfigProvider implements ConfigProviderInterface
{
    const CODE = CashOnDelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE;

    private $config;

    private $scopeConfig;

    private $configWriter;

    private $cacheCleanerFlag;

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
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
                self::CODE => [
                    'label' => $this->config->getValue('label'),
                    'description' => $this->config->getValue('description')
                ]
            ]
        ];
    }

    public function getConfigPath($config)
    {
        return 'payment/' . self::CODE . '/' . $config;
    }

    public function getConfigData($config)
    {
        return $this->scopeConfig->getValue($this->getConfigPath($config));
    }

    public function getPaymentMethodCode()
    {
        return self::CODE;
    }

    public function updateConfig($configs) {
        foreach($configs as $configCode => $configValue) {
            $this->setConfigData($configCode, $configValue);
        }

        $this->cacheCleanerFlag->addFlag();
    }

    public function setConfigData($config, $value)
    {
        $this->configWriter->save($this->getConfigPath($config),  $value, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeId = 0);
    }

    public function getTittle(){
        return $this->getConfigData('title');
    }

    public function getOrderStatus(){
        return $this->getConfigData('order_status');
    }
}
