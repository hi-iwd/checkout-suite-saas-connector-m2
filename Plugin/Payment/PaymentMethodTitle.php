<?php

namespace IWD\CheckoutConnector\Plugin\Payment;

use Closure;
use IWD\CheckoutConnector\Helper\PaymentMethod;
use Magento\Payment\Helper\Data;

/**
 * Class PaymentMethodTitle
 *
 * @package IWD\CheckoutConnector\Plugin\Payment
 */
class PaymentMethodTitle
{
    /**
     * @var PaymentMethod
     */
    private $paymentHelper;

    /**
     * PaymentMethodTitle constructor.
     *
     * @param PaymentMethod $paymentHelper
     */
    public function __construct(
        PaymentMethod $paymentHelper
    ) {
        $this->paymentHelper = $paymentHelper;
    }

    /**
     * @param Data $subject
     * @param Closure $proceed
     * @param bool $sorted
     * @param bool $asLabelValue
     * @param bool $withGroups
     * @param null $store
     * @return mixed
     */
    public function aroundGetPaymentMethodList(
        Data $subject,
        Closure $proceed,
        $sorted = true,
        $asLabelValue = false,
        $withGroups = false,
        $store = null
    ) {
        return $proceed($sorted,$asLabelValue,$withGroups,$store);
    }
}
