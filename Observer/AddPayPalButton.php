<?php

namespace IWD\CheckoutConnector\Observer;

use IWD\CheckoutConnector\Block\Shortcut\PayPalButton;
use IWD\CheckoutConnector\Block\Shortcut\AppleButton;
use IWD\CheckoutConnector\Block\Shortcut\GooglePayButton;
use Magento\Catalog\Block\ShortcutButtons;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class AddPayPalButton
 *
 * @package IWD\ApplePay\Observer
 */
class AddPayPalButton implements ObserverInterface
{
    /**
     * Add PayPal Button
     *
     * @param Observer $observer
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        // Check if we are not in Product Section
        if (!$observer->getData('is_catalog_product')) {
            /** @var ShortcutButtons $shortcutButtons */
            $shortcutButtons = $observer->getEvent()->getContainer();
            $shortcutPayPal = $shortcutButtons->getLayout()->createBlock(PayPalButton::class);
            $shortcutApplePay = $shortcutButtons->getLayout()->createBlock(AppleButton::class);
            $shortcutGPay = $shortcutButtons->getLayout()->createBlock(GooglePayButton::class);

            /** Add PayPal btn to Shortcuts Section */
            $shortcutButtons->addShortcut($shortcutPayPal);
            /** Add Apple Pay btn to Shortcuts Section */
            $shortcutButtons->addShortcut($shortcutApplePay);
            /** Add Google Pay btn to Shortcuts Section */
            $shortcutButtons->addShortcut($shortcutGPay);
        }
    }
}