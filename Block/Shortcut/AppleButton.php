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
class AppleButton extends Template implements ShortcutInterface
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

    public function getAlias()
    {
        return $this->getData(self::ALIAS_ELEMENT_INDEX);
    }

    protected function _toHtml()
    {
        if (!$this->helper->isEnable()) {
            return '';
        }

        return parent::_toHtml();
    }
}
