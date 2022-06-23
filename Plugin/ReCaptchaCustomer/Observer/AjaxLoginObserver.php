<?php

namespace IWD\CheckoutConnector\Plugin\ReCaptchaCustomer\Observer;

use IWD\CheckoutConnector\Helper\Data as Helper;
use Magento\Framework\Event\Observer;
use Magento\ReCaptchaCustomer\Observer\AjaxLoginObserver as MagentoAjaxLoginObserver;

/**
 * Class AjaxLoginObserver
 *
 * @package IWD\CheckoutConnector\Plugin\ReCaptchaCustomer\Observer
 */
class AjaxLoginObserver
{
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
     * @param MagentoAjaxLoginObserver $subject
     * @param \Closure $proceed
     * @param Observer $observer
     * @return void
     */
    public function aroundExecute(
        MagentoAjaxLoginObserver $subject,
        \Closure $proceed,
        Observer $observer
    ): void {
        if (!$this->helper->isEnable()) {
            $proceed($observer);
        }
    }
}
