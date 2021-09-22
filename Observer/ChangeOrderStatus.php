<?php

namespace IWD\CheckoutConnector\Observer;

use IWD\CheckoutConnector\Model\Ui\IWDCheckoutPayConfigProvider;
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

    /**
     * @var IWDCheckoutPayConfigProvider
     */
    private $IWDCheckoutPayConfigProvider;

    /**
     * ChangeOrderStatus constructor.
     *
     * @param OrderStatus $orderStatus
     * @param IWDCheckoutPayConfigProvider $IWDCheckoutPayConfigProvider
     */
    public function __construct(
        OrderStatus $orderStatus,
        IWDCheckoutPayConfigProvider $IWDCheckoutPayConfigProvider
    ) {
        $this->orderStatus = $orderStatus;
        $this->IWDCheckoutPayConfigProvider = $IWDCheckoutPayConfigProvider;
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
            $payment_method_code == $this->IWDCheckoutPayConfigProvider->getPaymentMethodCode()
            && $shipping_method_code != \IWD\CheckoutConnector\Model\Carrier\Subscription::CODE
        ) {
            $this->orderStatus->changeStatus($order);
        }
    }
}
