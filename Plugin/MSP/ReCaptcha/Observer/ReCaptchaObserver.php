<?php

namespace IWD\CheckoutConnector\Plugin\MSP\ReCaptcha\Observer;

use IWD\CheckoutConnector\Helper\Data as Helper;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Event\Observer;
use MSP\ReCaptcha\Observer\ReCaptchaObserver as ReCaptchaObserverObserver;

/**
 * Class ReCaptchaObserver
 *
 * @package IWD\CheckoutConnector\Plugin\MSP\ReCaptcha\Observer
 */
class ReCaptchaObserver
{
    public const AJAX_LOGIN_PATH = 'customer_ajax_login';

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @param Helper $helper
     */
    public function __construct(Helper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Disable standard reCaptcha validation if Dominate is enabled
     *
     * @param ReCaptchaObserverObserver $subject
     * @param \Closure $proceed
     * @param Observer $observer
     * @return void
     */
    public function aroundExecute(
        ReCaptchaObserverObserver $subject,
        \Closure $proceed,
        Observer $observer
    ): void {
        /** @var Action $controller */
        $controller = $observer->getControllerAction();

        if (!$this->helper->isEnable()
            || $controller->getRequest()->getFullActionName() != self::AJAX_LOGIN_PATH) {
            $proceed($observer);
        }
    }
}
