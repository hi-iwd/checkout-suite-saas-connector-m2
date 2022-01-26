<?php

namespace IWD\CheckoutConnector\ViewModel;

use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use IWD\CheckoutConnector\Block\Frame;

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
            'checkoutIframeId' => Frame::CHECKOUT_IFRAME_ID,
        ];
    }
}
