<?php

namespace IWD\CheckoutConnector\Block\PayPal;

use IWD\CheckoutConnector\Helper\Data as Helper;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutPayConfigProvider;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * Class Banner
 *
 * @package IWD\CheckoutConnector\Block\PayPal
 */
class Banner extends Template
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var IWDCheckoutPayConfigProvider
     */
    private $configProvider;

    /**
     * @var JsonHelper
     */
    private $jsonHelper;

	/**
	 * @var string|null
	 */
	private $placement;

    /**
     * Banner constructor.
     *
     * @param Context $context
     * @param Helper $helper
     * @param IWDCheckoutPayConfigProvider $configProvider
     * @param JsonHelper $jsonHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Helper $helper,
        IWDCheckoutPayConfigProvider $configProvider,
        JsonHelper $jsonHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->helper = $helper;
        $this->configProvider = $configProvider;
        $this->jsonHelper = $jsonHelper;

	    $this->placement = $data['placement'] ?? null;
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        if (!$this->helper->isEnable()) {
            return '';
        }

        return parent::_toHtml();
    }

	/**
	 * Get banner config
	 *
	 * @return bool|false|string
	 */
    public function getJsonConfig()
    {
		$config = $this->configProvider->getPayPalBannerConfig();

        $implementationArray = [
            'IWD_CheckoutConnector/js/view/payment/paypal/banner' => [
				'config' => $config->{$this->placement} ?? null
            ]
        ];

        return $this->jsonHelper->jsonEncode($implementationArray);
    }
}
