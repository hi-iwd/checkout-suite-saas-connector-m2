<?php

namespace IWD\CheckoutConnector\Model\Address;

use Magento\Quote\Model\Cart\ShippingMethodConverter;
use Magento\Quote\Model\Quote\TotalsCollector;

/**
 * Class ShippingMethods
 *
 * @package IWD\CheckoutConnector\Model\Address
 */
class ShippingMethods
{
    /**
     * @var Addresses
     */
    private $addresses;

    /**
     * @var TotalsCollector
     */
    private $totalsCollector;

    /**
     * Shipping method converter
     *
     * @var ShippingMethodConverter
     */
    private $converter;

    /**
     * ShippingMethods constructor.
     *
     * @param Addresses $addresses
     * @param TotalsCollector $totalsCollector
     * @param ShippingMethodConverter $converter
     */
    public function __construct(
        Addresses $addresses,
        TotalsCollector $totalsCollector,
        ShippingMethodConverter $converter
    ) {
        $this->addresses = $addresses;
        $this->totalsCollector = $totalsCollector;
        $this->converter = $converter;
    }

    /**
     * @param $quote
     * @return array
     */
    public function getShippingMethods($quote)
    {
        $output = [];
        $shippingAddress = $quote->getShippingAddress();

        $shippingAddress->setCollectShippingRates(true);

        $this->totalsCollector->collectAddressTotals($quote, $shippingAddress);
        $shippingRates = $shippingAddress->getGroupedAllShippingRates();

        foreach ($shippingRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                $output[] = $this->converter->modelToDataObject($rate, $quote->getQuoteCurrencyCode());
            }
        }

        $result = [];

        foreach ($output as $key => $method) {
            if($method->getAvailable()) {
                $result[] = [
                    'method_code'   => $method->getCarrierCode().'_'.$method->getMethodCode(),
                    'carrier_title' => $method->getCarrierTitle(),
                    'method_title'  => $method->getMethodTitle(),
                    'amount'        => (number_format($method->getAmount(),2,'.','')),
                ];
            }
        }

        return $result;
    }

    /**
     * @param $quote
     * @return mixed
     */
    public function getSelectedShippingMethod($quote)
    {
        $shippingAddress = $quote->getShippingAddress();
        $shippingMethod = [];

        if($shippingAddress->getShippingMethod()) {
            $shippingMethod['method_code'] = $shippingAddress->getShippingMethod();
            $shippingMethod['amount'] = number_format($shippingAddress->getShippingAmount(),2,'.','');
            $shippingMethod['carrier_title'] = $shippingAddress->getShippingDescription();
        }

        return $shippingMethod;
    }

    /**
     * @param $shippingMethods
     * @param $selectedShippingMethod
     * @return bool
     */
    public function isSelectedShippingMethodAvailable($shippingMethods, $selectedShippingMethod) {
        foreach($shippingMethods as $shippingMethod) {
            if($shippingMethod['method_code'] === $selectedShippingMethod['method_code']) {
                return true;
            }
        }

        return false;
    }
}