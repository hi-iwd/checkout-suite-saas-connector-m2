<?php

namespace IWD\CheckoutConnector\Plugin\Checkout\CustomerData;

use Magento\Checkout\CustomerData\Cart as MagentoCart;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutPayConfigProvider;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Cart
 *
 * @package IWD\CheckoutConnector\Plugin\Checkout
 */
class Cart
{
    /**
     * @var IWDCheckoutPayConfigProvider
     */
    private $configProvider;

    /**
     * Cart constructor.
     *
     * @param IWDCheckoutPayConfigProvider $configProvider
     */
    public function __construct(
        IWDCheckoutPayConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    /**
     * @param MagentoCart $subject
     * @param array $result
     * @return array
     * @throws LocalizedException
     */
    public function afterGetSectionData(MagentoCart $subject, array $result)
    {
        $config = $this->configProvider;

        $result['paypal_credit_msg_config'] = [
            'status'             => $config->getConfigData('paypal_credit_status'),
            'container_id'       => $config->getGeneratedContainerId(),
            'grand_total_amount' => $config->getGrandTotalAmount(),
            'logo_type'          => $config->getConfigData('credit_msg_logo_type'),
            'logo_position'      => $config->getConfigData('credit_msg_logo_position'),
            'text_color'         => $config->getConfigData('credit_msg_text_color'),
        ];

        return $result;
    }
}
