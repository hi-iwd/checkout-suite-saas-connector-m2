<?php

namespace IWD\CheckoutConnector\ViewModel;

use IWD\CheckoutConnector\Model\Ui\IWDCheckoutPayConfigProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;

class ProductSku implements ArgumentInterface
{
    /**
     * @var Registry
     */
    private $registry;
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param Registry $registry
     */
    public function __construct(
        Registry             $registry,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->registry = $registry;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return array
     */
    public function getProductData(): array
    {
        $currentProduct = $this->registry->registry('current_product');
        if ('configurable' == $currentProduct->getTypeId()) {
            $data = [];
            $childProducts = $currentProduct->getTypeInstance()->getUsedProducts($currentProduct);
            foreach ($childProducts as $key => $simpleProduct) {
                $data[$key]['id'] = $simpleProduct->getId();
                $data[$key]['sku'] = $simpleProduct->getSku();
            }
            return $data;
        }

        return [['id' => $currentProduct->getId(), 'sku' => $currentProduct->getSku()]];
    }

    /**
     * @return array
     */
    public function getBtnConfiguration()
    {
        return [
            'btn_shape' => $this->scopeConfig->getValue('payment/' . IWDCheckoutPayConfigProvider::CODE . '/btn_shape'),
            'btn_color' => $this->scopeConfig->getValue('payment/' . IWDCheckoutPayConfigProvider::CODE . '/btn_color')
        ];
    }

    public function isEnable($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            'iwd_checkout_connector/general/enable_subscription',
            ScopeInterface::SCOPE_STORE
        );
    }
}
