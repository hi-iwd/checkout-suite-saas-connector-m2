<?php

namespace IWD\CheckoutConnector\Model\Cart;

use Magento\Directory\Model\Currency;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Quote\Model\Quote;
use Magento\Tax\Model\Config as TaxConfig;

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
     * @var TaxConfig
     */
	private $taxConfig;

	/**
     * CartTotals constructor.
     *
	 * @param  Currency  $currency
	 * @param  ModuleListInterface  $moduleList
	 * @param  TaxConfig  $taxConfig
     */
    public function __construct(
        Currency $currency,
	    ModuleListInterface $moduleList,
        TaxConfig $taxConfig
    ) {
        $this->currency = $currency;
	    $this->moduleList = $moduleList;
	    $this->taxConfig = $taxConfig;
    }

    /**
     * @param $quote Quote
     *
     * @return array
     */
    public function getTotals($quote, $additional = true)
    {
        $quoteShippingAddress = $quote->getShippingAddress();

        $totals = [
            'version'           => $additional ? $this->moduleList->getOne('IWD_CheckoutConnector')['setup_version'] : null,
            'is_virtual'        => $quote->isVirtual(),
            'quote_currency'    => $quote->getQuoteCurrencyCode(),
            'currency'          => $quote->getBaseCurrencyCode(),
            'subtotal'          => $this->priceFormat($this->getSubtotal($quote)),
            'shipping'          => $this->priceFormat($this->getShippingAmount($quote)),
            'tax'               => $this->priceFormat($quoteShippingAddress->getTaxAmount()),
            'discount'          => $this->priceFormat(abs($quoteShippingAddress->getDiscountAmount())),
            'quote_grand_total' => $this->priceFormat($quote->getGrandTotal()),
            'grand_total'       => $this->priceFormat($quote->getBaseGrandTotal()),
            'coupon_code'       => $quote->getCouponCode(),
            'currency_symbol'   => $this->currency->getCurrencySymbol(),
            'country'           => $additional ? $quoteShippingAddress->getCountryId() : null
        ];

		if ($this->taxConfig->displayCartSubtotalBoth($quote->getStoreId())) {
			$totals['subtotal_excl_tax'] = $this->priceFormat($quote->getSubtotal());
		}

	    if ($this->taxConfig->displayCartShippingBoth($quote->getStoreId())) {
		    $totals['shipping_excl_tax'] = $this->priceFormat($quoteShippingAddress->getShippingAmount());
	    }

        if ($this->taxConfig->displayCartTaxWithGrandTotal($quote->getStoreId())) {
            $totals['grand_total_excl_tax'] = $this->priceFormat($quote->getGrandTotal() - $quoteShippingAddress->getTaxAmount());
        }

        return $totals;
    }

	/**
	 * @param $quote Quote
	 *
	 * @return mixed
	 */
	protected function getSubtotal($quote)
	{
		if ($this->taxConfig->displayCartSubtotalInclTax($quote->getStoreId())
		    || $this->taxConfig->displayCartSubtotalBoth($quote->getStoreId())) {
			return $quote->getShippingAddress()->getSubtotalInclTax();
		}

		return $quote->getSubtotal();
	}

	/**
	 * @param $quote Quote
	 *
	 * @return mixed
	 */
	protected function getShippingAmount($quote)
	{
		$quoteShippingAddress = $quote->getShippingAddress();

		if ($this->taxConfig->displayCartShippingInclTax($quote->getStoreId())
		    || $this->taxConfig->displayCartShippingBoth($quote->getStoreId())) {
			return $quoteShippingAddress->getShippingInclTax();
		}

		return $quoteShippingAddress->getShippingAmount();
	}

	/**
	 * @param $price
	 *
	 * @return string
	 */
	protected function priceFormat($price)
	{
		return number_format($price, 2, '.', '');
	}
}
