<?php

namespace IWD\CheckoutConnector\Block\Checkout;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\OrderRepository;

/**
 * Class Success
 *
 * @package IWD\CheckoutConnector\Block\Checkout
 */
class Success extends Template
{
    private $checkoutSession;
    private $orderRepository;

	public function __construct(
		Template\Context $context,
		Session $checkoutSession,
		OrderRepository $orderRepository,
		array $data = [])
	{
		parent::__construct($context, $data);

		$this->checkoutSession = $checkoutSession;
		$this->orderRepository = $orderRepository;
	}

	/**
     * @return array|string[]
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function getPaymentMethodDetails()
    {
	    $paymentDetails = [];
	    $orderId        = $this->checkoutSession->getLastOrderId();
	    $order          = $this->orderRepository->get($orderId);
	    $additionalInfo = $order->getPayment()->getAdditionalInformation();

	    if (isset($additionalInfo['iwd_method_code']) && $additionalInfo['iwd_method_code'] === 'pay_upon_invoice') {
		    $paymentDetails['Important'] = __('Please review the payment instructions that have been sent to your email.');
	    }

	    return $paymentDetails;
    }
}