<?php

namespace IWD\CheckoutConnector\Model\Cart;

use Magento\Directory\Model\Currency;
use Magento\Framework\Module\ModuleListInterface;

/**
 * Class CartTotals
 *
 * @package IWD\CheckoutConnector\Model\Cart
 */
class CartTotals
{
    /**
     * @var Currency
     */
    private $currency;

	/**
	 * @var ModuleListInterface
	 */
	private $moduleList;

	/**
     * CartTotals constructor.
     *
	 * @param  Currency  $currency
	 * @param  ModuleListInterface  $moduleList
     */
    public function __construct(
        Currency $currency,
	    ModuleListInterface $moduleList
    ) {
        $this->currency = $currency;
	    $this->moduleList = $moduleList;
    }

    /**
     * @param $quote
     * @return array
     */
    public function getTotals($quote)
    {
        $quoteShippingAddress = $quote->getShippingAddress();

        return [
			'version'           => $this->moduleList->getOne('IWD_CheckoutConnector')['setup_version'],
            'is_virtual'        => $quote->isVirtual(),
            'quote_currency'    => $quote->getQuoteCurrencyCode(),
            'currency'          => $quote->getBaseCurrencyCode(),
            'subtotal'          => number_format($quote->getSubtotal(),2,'.',''),
            'shipping'          => number_format($quoteShippingAddress->getShippingAmount(),2,'.',''),
            'tax'               => number_format($quoteShippingAddress->getTaxAmount(),2,'.',''),
            'discount'          => number_format(abs($quoteShippingAddress->getDiscountAmount()),2,'.',''),
            'quote_grand_total' => number_format($quote->getGrandTotal(),2,'.',''),
            'grand_total'       => number_format($quote->getBaseGrandTotal(),2,'.',''),
            'coupon_code'       => $quote->getCouponCode(),
            'currency_symbol' => $this->currency->getCurrencySymbol(),
            'country'         => $quoteShippingAddress->getCountryId()
        ];
    }
}