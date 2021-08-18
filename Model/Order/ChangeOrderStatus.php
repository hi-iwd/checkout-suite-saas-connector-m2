<?php

namespace IWD\CheckoutConnector\Model\Order;

use Magento\Framework\HTTP\Client\Curl;
use IWD\CheckoutConnector\Gateway\Config\Config;
use IWD\CheckoutConnector\Helper\Data;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class ChangeOrderStatus
 * @package IWD\CheckoutConnector\Model\Order
 */
class ChangeOrderStatus
{
    const INTEGRATION_SECRET = 'integration_secret';
    const INTEGRATION_KEY = 'integration_key';
    const ORDER_STATUS = 'order_status';
    const ORDER_ID = 'order_id';

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
     * ChangeOrderStatus constructor.
     * @param Curl $curl
     * @param Config $config
     * @param Data $helper
     */
    public function __construct(
        Curl $curl,
        Config $config,
        Data $helper
    )
    {
        $this->curl = $curl;
        $this->config = $config;
        $this->helper = $helper;
    }

    /**
     * @param $order
     */
    public function changeStatus($order)
    {
        $body = [
            self::ORDER_ID           => $order->getIncrementId(),
            self::ORDER_STATUS       => $order->getState(),
            self::INTEGRATION_KEY    => $this->config->getIntegrationApiKey(),
            self::INTEGRATION_SECRET => $this->config->getIntegrationApiSecret()
        ];

        $url = $this->helper->getOrderStatusUpdateUrl();

        $this->curl->post($url, $body);
        $body = $this->curl->getBody();
        $response = json_decode($body, true);

        if (empty($response)) {
            throw new LocalizedException(__("Connection error!"));
        }

        if (is_array($response) && !empty( $response["resultCode"]) && $response["resultCode"] == 0) {
            throw new LocalizedException($response["errorMsg"]);
        }
    }
}
