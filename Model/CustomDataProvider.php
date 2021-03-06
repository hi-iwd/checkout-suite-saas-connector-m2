<?php

namespace IWD\CheckoutConnector\Model;

use IWD\CheckoutConnector\Block\Checkout\CustomAddressData;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class CustomDataProvider
 *
 * @package IWD\CheckoutConnector\Model
 */
class CustomDataProvider
{
    /**#@+
     * Allowed Custom Data Render Areas
     */
    public const BEFORE_LOGIN_FORM       = 'before_login_form';
    public const AFTER_LOGIN_FORM        = 'after_login_form';
    public const BEFORE_SHIPPING_ADDRESS = 'before_shipping_address';
    public const AFTER_SHIPPING_ADDRESS  = 'after_shipping_address';
    public const BEFORE_BILLING_ADDRESS  = 'before_billing_address';
    public const AFTER_BILLING_ADDRESS   = 'after_billing_address';
    public const BEFORE_DELIVERY_METHODS = 'before_delivery_methods';
    public const AFTER_DELIVERY_METHODS  = 'after_delivery_methods';
    public const BEFORE_PAYMENT_METHODS  = 'before_payment_methods';
    public const AFTER_PAYMENT_METHODS   = 'after_payment_methods';
    public const BEFORE_CART_ITEMS       = 'before_cart_items';
    public const AFTER_CART_ITEMS        = 'after_cart_items';
    public const BEFORE_TOTALS           = 'before_totals';
    public const AFTER_TOTALS            = 'after_totals';
    public const BEFORE_PLACE_ORDER_BTN  = 'before_place_order_btn';
    public const AFTER_PLACE_ORDER_BTN   = 'after_place_order_btn';
    /**#@-*/

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * CustomDataProvider constructor.
     *
     * @param CartRepositoryInterface $cartRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        OrderRepositoryInterface $orderRepository,
        PageFactory $resultPageFactory
    ) {
        $this->cartRepository = $cartRepository;
        $this->orderRepository = $orderRepository;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Pass Custom Data fields to Checkout
     *
     * @param $quote
     * @return array
     */
    public function getData($quote)
    {
        $data = [];

        /**
         * Uncomment lines 85-96 to make Company Field Available in the Address Section
         *
         * @var $block CustomAddressData
         */
        //$resultPage = $this->resultPageFactory->create();
        //$block = $resultPage->getLayout()->createBlock('IWD\CheckoutConnector\Block\Checkout\CustomAddressData');
        //
        //if (!empty($block)) {
        //    $content = $block
        //        ->setQuote($quote)
        //        ->setTemplate('custom_address_fields.phtml');
        //
        //    // Add Company Field To Billing And Shipping Addresses
        //    $data[self::AFTER_SHIPPING_ADDRESS] = $content->setAddressType('shipping_address')->toHtml();
        //    $data[self::AFTER_BILLING_ADDRESS]  = $content->setAddressType('billing_address')->toHtml();
        //}

        return $data;
    }

    /**
     * Save Custom Data From Checkout to Quote
     *
     * @param $quote
     * @param $data
     */
    public function saveDataToQuote($quote, $data)
    {
        foreach((array) $data as $key => $value) {
            if($key === 'shipping_address') {
                $updatedShippingAddress = $this->setDataToObject($quote->getShippingAddress(), $value);
                $quote->setShippingAddress($updatedShippingAddress);
            }
            elseif ($key === 'billing_address') {
                $updatedBillingAddress = $this->setDataToObject($quote->getBillingAddress(), $value);
                $quote->setBillingAddress($updatedBillingAddress);
            }
            elseif ($key === 'customer') {
                $updatedCustomer = $this->setDataToObject($quote->getCustomer(), $value);
                $quote->setCustomer($updatedCustomer);
            }
            else {
                $quote->setData($key, $value);
            }
        }

        $this->cartRepository->save($quote);
    }

    /**
     * Save Custom Data From Checkout to Order
     *
     * @param $order
     * @param $data
     */
    public function saveDataToOrder($order, $data)
    {
        $order = $this->setDataToObject($order, $data);

        $this->orderRepository->save($order);
    }

    /**
     * @param $object
     * @param $data
     * @return mixed
     */
    private function setDataToObject($object, $data)
    {
        foreach((array) $data as $key => $value) {
            $object->setData($key, $value);
        }

        return $object;
    }
}