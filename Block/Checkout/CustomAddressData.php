<?php

namespace IWD\CheckoutConnector\Block\Checkout;

use Magento\Quote\Model\Quote;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class CustomAddressData
 *
 * @package IWD\CheckoutConnector\Block\Checkout
 */
class CustomAddressData extends Template
{
    /**
     * @var Quote
     */
    private $quote;

    /**
     * @var string
     */
    private $addressType;

    /**
     * CustomAddressData constructor.
     *
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @param $quote
     * @return CustomAddressData
     */
    public function setQuote($quote)
    {
        $this->quote = $quote;

        return $this;
    }

    /**
     * @param $type
     * @return CustomAddressData
     */
    public function setAddressType($type)
    {
        $this->addressType = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddressType()
    {
        return $this->addressType;
    }

    /**
     * @return Quote\Address
     */
    public function getAddress()
    {
        return $this->addressType === 'shipping_address'
            ? $this->quote->getShippingAddress()
            : $this->quote->getBillingAddress();
    }
}