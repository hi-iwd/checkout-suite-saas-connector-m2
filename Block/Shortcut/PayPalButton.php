<?php

namespace IWD\CheckoutConnector\Block\Shortcut;

use IWD\CheckoutConnector\Helper\Data as Helper;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutPayConfigProvider;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Block\ShortcutInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * Class Button
 *
 * @package IWD\CheckoutConnector\Block\Shortcut
 */
class PayPalButton extends Template implements ShortcutInterface
{
    const ALIAS_ELEMENT_INDEX = 'alias';

    /**
     * @var Session
     */
    private $session;

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
     * Button constructor.
     *
     * @param Context $context
     * @param Session $session
     * @param Helper $helper
     * @param IWDCheckoutPayConfigProvider $configProvider
     * @param JsonHelper $jsonHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $session,
        Helper $helper,
        IWDCheckoutPayConfigProvider $configProvider,
        JsonHelper $jsonHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->session = $session;
        $this->helper = $helper;
        $this->configProvider = $configProvider;
        $this->jsonHelper = $jsonHelper;
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
     * @return string
     */
    public function getClientId()
    {
        return $this->configProvider->getConfigData('client_id');
    }

    /**
     * @return string
     */
    public function getPayPalBnCode() {
        return $this->configProvider->getConfigData('bn_code');
    }

    /**
     * @return string
     */
    public function getPaypalStatus(){
        return $this->configProvider->getConfigData('status');
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getContainerId()
    {
        return $this->configProvider->getGeneratedContainerId();
    }

    /**
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->configProvider->getCurrencyCode();
    }

    /**
     * @return mixed|string
     */
    public function getAlias()
    {
        return $this->getData(self::ALIAS_ELEMENT_INDEX);
    }

    /**
     * Get button config
     *
     * @param $containerId
     * @return bool|false|string
     */
    public function getJsonConfig($containerId)
    {
        $config = $this->configProvider->getButtonConfig($containerId);

        $implementationArray = [
            'IWD_CheckoutConnector/js/view/payment/shortcut/iwd_paypal_button' => $config
        ];

        return $this->jsonHelper->jsonEncode($implementationArray);
    }
}
