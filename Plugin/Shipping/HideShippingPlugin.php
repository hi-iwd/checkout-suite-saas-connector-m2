<?php

namespace IWD\CheckoutConnector\Plugin\Shipping;

class HideShippingPlugin
{
    /**
     * @param \Magento\Shipping\Model\Shipping $subject
     * @param \Closure $proceed
     * @param $carrierCode
     * @param $request
     * @return false|mixed
     */
    public function aroundCollectCarrierRates(
        \Magento\Shipping\Model\Shipping $subject,
        \Closure                         $proceed,
                                         $carrierCode,
                                         $request
    )
    {
        if ($carrierCode == 'subscription') {
            return false;
        }

        return $proceed($carrierCode, $request);
    }
}