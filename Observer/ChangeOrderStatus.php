<?php

namespace IWD\CheckoutConnector\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use IWD\CheckoutConnector\Model\Order\ChangeOrderStatus as OrderStatus;

/**
 * Class ChangeOrderStatus
 * @package IWD\CheckoutConnector\Observer
 */
class ChangeOrderStatus implements ObserverInterface
{
    /**
     * @var OrderStatus
     */
    private $orderStatus;

    private $iwdPaymentMethods = [
        'iwd_checkout_pay',
        'iwd_checkout_offline_pay_checkmo',
        'iwd_checkout_offline_pay_zero',
        'iwd_checkout_offline_pay_cashondelivery',
        'iwd_checkout_offline_pay_banktransfer',
        'iwd_checkout_offline_pay_purchaseorder',
        'iwd_checkout_offline_pay_custom',
        'iwd_checkout_multiple_payment',
    ];

    /**
     * ChangeOrderStatus constructor.
     * @param OrderStatus $orderStatus
     */
    public function __construct(
        OrderStatus $orderStatus
    ) {
        $this->orderStatus = $orderStatus;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $payment_method_code = $order->getPayment()->getMethodInstance()->getCode();
        $shipping_method_code = $order->getShippingMethod();
        if (
            in_array($payment_method_code,$this->iwdPaymentMethods)
            && $shipping_method_code != \IWD\CheckoutConnector\Model\Carrier\Subscription::CODE
        ) {
            $this->orderStatus->changeStatus($order);
        }
    }
}