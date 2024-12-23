<?php

namespace IWD\CheckoutConnector\Plugin\Payment;

use Magento\Framework\App\RequestInterface;

/**
 * Class OfflinePaymentsRestriction
 *
 * @package IWD\CheckoutConnector\Plugin\Payment
 */
class OfflinePaymentsRestriction
{

	private const ORDER_CREATION_PATH = '/V1/iwd-checkout/offline-order-create';

	/**
	 * @var RequestInterface
	 */
	protected $request;

	/**
	 * @var array|string[]
	 */
	private array $offlinePaymentMethods = [
		'iwd_checkout_offline_pay_checkmo',
		'iwd_checkout_offline_pay_zero',
		'iwd_checkout_offline_pay_cashondelivery',
		'iwd_checkout_offline_pay_banktransfer',
		'iwd_checkout_offline_pay_purchaseorder',
		'iwd_checkout_offline_pay_custom',
		'iwd_checkout_multiple_payment',
	];

	/**
	 * @param RequestInterface $request
	 */
	public function __construct(
		RequestInterface $request,
	) {
		$this->request = $request;
	}

	/**
	 * @param          $subject
	 * @param callable $proceed
	 * @param          $quote
	 *
	 * @return false
	 */
	public function aroundIsAvailable($subject, callable $proceed, $quote = null)
	{
		if (in_array($subject->getCode(), $this->offlinePaymentMethods)
		    && strpos($this->request->getPathInfo(), self::ORDER_CREATION_PATH) === false) {
			return false;
		}

		return $proceed($quote);
	}

}