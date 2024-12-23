<?php

namespace IWD\CheckoutConnector\ViewModel;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutPayConfigProvider;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ProductMsg
 *
 * This class is responsible for handling the configuration and PayPal availability for a product.
 *
 * @package IWD\CheckoutConnector\ViewModel
 */
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
	 * @return string
	 * @throws NoSuchEntityException
	 */
    public function getBtnConfiguration()
    {
	    $btn = [
		    'IWD_CheckoutConnector/js/view/product/paypal_msg' => [
			    'config' => [
				    'merchant_id'           => $this->IWDCheckoutPayConfigProvider->getConfigData('merchant_id'),
				    'client_id'             => $this->IWDCheckoutPayConfigProvider->getConfigData('client_id'),
				    'logoConfig'            => [
					    'position' => $this->IWDCheckoutPayConfigProvider->getConfigData('credit_msg_logo_position'),
					    'type'     => $this->IWDCheckoutPayConfigProvider->getConfigData('credit_msg_logo_type'),
				    ],
				    'color'                 => $this->IWDCheckoutPayConfigProvider->getConfigData('credit_msg_text_color'),
				    'currency'              => $this->_storeManager->getStore()->getCurrentCurrencyCode(),
				    'msg_configurator_data' => $this->IWDCheckoutPayConfigProvider->getConfigData('paypal_credit_msg_configurator_data')
					    ? json_decode($this->IWDCheckoutPayConfigProvider->getConfigData('paypal_credit_msg_configurator_data')) : null,
			    ],
		    ],
	    ];

        return $this->jsonHelper->jsonEncode($btn);
    }

	/**
	 * Checks if PayPal is enabled.
	 *
	 * @return bool Returns true if PayPal is enabled, false otherwise.
	 */
	public function isPayPalEnabled()
	{
		return (bool) $this->IWDCheckoutPayConfigProvider->getConfigData('status');
	}
}