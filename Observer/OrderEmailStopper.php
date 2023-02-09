<?php

namespace IWD\CheckoutConnector\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use Throwable;

/**
 * Class OrderEmailStopper
 *
 * @package IWD\CheckoutConnector\Observer
 */
class OrderEmailStopper implements ObserverInterface
{
	/**
	 * @param  Observer  $observer
	 *
	 * @return void
	 * @throws LocalizedException
	 */
    public function execute(Observer $observer)
    {
		// Stop Order Confirmation Emails for PayPal Pay Upon Invoice.
	    // Send only Invoice Email with RatePay Payment Instructions which comes via webhook from Dominate App.
		try {
			/** @var Order $order */
			$order = $observer->getEvent()->getOrder();
			$additionalInfo = $order->getPayment()->getAdditionalInformation();

			if (isset($additionalInfo['iwd_method_code']) && $additionalInfo['iwd_method_code'] === 'pay_upon_invoice') {
				$order->setCanSendNewEmailFlag(false);
				$order->setSendEmail(false);
				$order->save();
			}
		} catch (Throwable $e) {}
    }
}