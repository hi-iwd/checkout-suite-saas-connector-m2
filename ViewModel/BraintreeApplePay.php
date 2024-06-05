<?php

namespace IWD\CheckoutConnector\ViewModel;

use IWD\CheckoutConnector\Helper\Data;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Class BraintreeApplePay
 *
 * @package IWD\CheckoutConnector\ViewModel
 */
class BraintreeApplePay implements ArgumentInterface
{
    /**
     * @var JsonHelper
     */
    private $jsonHelper;

    /**
     * @param JsonHelper $jsonHelper
     */
    public function __construct(
        JsonHelper $jsonHelper
    )
    {
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @return string
     */
    public function getBtnConfiguration()
    {
        $config = $this->getConfig();
        $btn = [
            'IWD_CheckoutConnector/js/view/payment/braintree/braintree_apple_pay' => $config
        ];

        return $this->jsonHelper->jsonEncode($btn);
    }

    /**
     * @return array
     */
    private function getConfig(): array
    {
        return [
            'checkoutIframeId' => Data::IWD_CHECKOUT_IFRAME_ID,
        ];
    }
}
