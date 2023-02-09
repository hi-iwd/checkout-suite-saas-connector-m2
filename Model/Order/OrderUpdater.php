<?php

namespace IWD\CheckoutConnector\Model\Order;

use IWD\CheckoutConnector\Model\Carrier\Subscription;
use Magento\Framework\HTTP\Client\Curl;
use IWD\CheckoutConnector\Gateway\Config\Config;
use IWD\CheckoutConnector\Helper\Data;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;

/**
 * Class ChangeOrderStatus
 * @package IWD\CheckoutConnector\Model\Order
 */
class OrderUpdater
{
    const INTEGRATION_SECRET = 'integration_secret';
    const INTEGRATION_KEY = 'integration_key';
    const ORDER_STATUS = 'order_status';
    const ORDER_ID = 'order_id';
	const SHIPMENT_TRACKER = 'shipment_tracker';

	/**
	 * @var string[]
	 */
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
     * @var Curl
     */
    private $curl;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Data
     */
    private $helper;

	/**
	 * @var Order
	 */
	private $order = null;

	/**
	 * @var array
	 */
	private $shipmentTracker = [];

	/**
     * ChangeOrderStatus constructor.
     *
     * @param Curl $curl
     * @param Config $config
     * @param Data $helper
     */
    public function __construct(
        Curl $curl,
        Config $config,
        Data $helper
    ) {
        $this->curl = $curl;
        $this->config = $config;
        $this->helper = $helper;
    }

	/**
	 * @return void
	 * @throws LocalizedException
	 */
    public function update()
    {
	    if (!$this->isUpdateAllowed()) return;

	    $url = $this->helper->getOrderUpdateUrl();
		$body = $this->getRequestBody();

        $this->curl->post($url, $body);
        $body = $this->curl->getBody();
        $response = json_decode($body, true);

		/** Clear Shipment Tracker Information after the API Request*/
	    $this->shipmentTracker = [];

        if (empty($response)) {
            throw new LocalizedException(__("Connection error!"));
        }

        if (is_array($response) && !empty($response["resultCode"]) && $response["resultCode"] == 0) {
            throw new LocalizedException($response["errorMsg"]);
        }
    }

	/**
	 * @return bool
	 * @throws LocalizedException
	 */
	private function isUpdateAllowed()
	{
		return $this->order
		       && in_array($this->order->getPayment()->getMethodInstance()->getCode(), $this->iwdPaymentMethods)
		       && $this->order->getShippingMethod() !== Subscription::CODE;
	}

	/**
	 * @return array
	 */
	private function getRequestBody()
	{
		return [
			self::ORDER_ID           => $this->order->getIncrementId(),
			self::ORDER_STATUS       => $this->order->getState(),
			self::INTEGRATION_KEY    => $this->config->getIntegrationApiKey($this->order->getStoreId()),
			self::INTEGRATION_SECRET => $this->config->getIntegrationApiSecret($this->order->getStoreId()),
			self::SHIPMENT_TRACKER   => $this->shipmentTracker
		];
	}

	/**
	 * @param $tracker array
	 *
	 * @return void
	 */
	public function setShipmentTracker($tracker)
	{
		$this->shipmentTracker = $tracker;
	}

	/**
	 * @param $order Order
	 *
	 * @return void
	 */
	public function setOrder($order)
	{
		$this->order = $order;
	}
}
