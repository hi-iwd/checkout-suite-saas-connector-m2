<?php

namespace IWD\CheckoutConnector\Observer;

use Magento\Framework\Event\ObserverInterface;

class ConvertQuoteToOrder implements ObserverInterface
{

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var \Magento\Quote\Model\Quote $quote
         * @var \Magento\Sales\Model\Order $order
         */
        $quote = $observer->getEvent()->getQuote();
        $order = $observer->getEvent()->getOrder();

        $order->setDominateAdditionalFields($quote->getDominateAdditionalFields());
    }
}
