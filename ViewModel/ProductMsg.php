<?php

namespace IWD\CheckoutConnector\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutPayConfigProvider;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Store\Model\StoreManagerInterface;

class ProductMsg implements ArgumentInterface
{

    /**
     * @var JsonHelper
     */
    private $jsonHelper;

    /**
     * @var IWDCheckoutPayConfigProvider
     */
    private $IWDCheckoutPayConfigProvider;

    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @param JsonHelper $jsonHelper
     * @param IWDCheckoutPayConfigProvider $IWDCheckoutPayConfigProvider
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        JsonHelper                   $jsonHelper,
        IWDCheckoutPayConfigProvider $IWDCheckoutPayConfigProvider,
        StoreManagerInterface        $storeManager
    )
    {
        $this->jsonHelper = $jsonHelper;
        $this->IWDCheckoutPayConfigProvider = $IWDCheckoutPayConfigProvider;
        $this->_storeManager = $storeManager;
    }

    /**
     * @return mixed
     */
    public function getBtnConfiguration()
    {
        $btn = [
            'IWD_CheckoutConnector/js/view/product/paypal_msg' => [
                'config' => [
                    'merchant_id' => $this->IWDCheckoutPayConfigProvider->getConfigData('merchant_id'),
                    'client_id' => $this->IWDCheckoutPayConfigProvider->getConfigData('client_id'),
                    'logoConfig' => [
                        'position' => $this->IWDCheckoutPayConfigProvider->getConfigData('credit_msg_logo_position'),
                        'type' => $this->IWDCheckoutPayConfigProvider->getConfigData('credit_msg_logo_type')
                    ],
                    'color' => $this->IWDCheckoutPayConfigProvider->getConfigData('credit_msg_text_color'),
                    'currency' => $this->_storeManager->getStore()->getCurrentCurrencyCode()
                ]
            ]
        ];

        return $this->jsonHelper->jsonEncode($btn);
    }

}