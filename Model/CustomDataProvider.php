<?php

namespace IWD\CheckoutConnector\Model;

use Exception;
use IWD\CheckoutConnector\Block\Checkout\CustomAddressData;
use Magento\Framework\View\Result\PageFactory;
use Magento\Newsletter\Model\Subscriber;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\ResourceModel\Address;
use IWD\CheckoutConnector\Helper\Order as OrderHelper;

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
	 * @var Subscriber
	 */
	private $subscriber;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

    /**
     * @var CustomerFactory
     */
	private $customerFactory;

    /**
     * @var AddressFactory
     */
	private $addressFactory;

    /**
     * @var Address
     */
	private $addressResource;

    /**
     * @var OrderHelper
     */
	private $orderHelper;

    /**
     * CustomDataProvider constructor.
     *
     * @param CartRepositoryInterface $cartRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param PageFactory $resultPageFactory
     * @param Subscriber $subscriber
     * @param LoggerInterface $logger
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        OrderRepositoryInterface $orderRepository,
        PageFactory $resultPageFactory,
	    Subscriber $subscriber,
	    LoggerInterface $logger,
        CustomerFactory $customerFactory,
        AddressFactory $addressFactory,
        Address $addressResource,
        OrderHelper $orderHelper
    ) {
	    $this->cartRepository    = $cartRepository;
	    $this->orderRepository   = $orderRepository;
	    $this->resultPageFactory = $resultPageFactory;
	    $this->subscriber        = $subscriber;
	    $this->logger            = $logger;
	    $this->customerFactory   = $customerFactory;
	    $this->addressFactory    = $addressFactory;
	    $this->addressResource   = $addressResource;
	    $this->orderHelper       = $orderHelper;
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

	/**
	 * Process Dominate Data
	 *
	 * @param $order OrderInterface
	 * @param $data
	 */
	public function processDominateData($order, $data)
	{
		foreach ((array) $data as $key => $value) {
			if ($key === 'subscribe_to_newsletter') {
				$this->subscribeToNewsletter($order);
			}
            elseif ($key === 'create_customer_account') {
                $this->createCustomerAccount($order);
            }
		}
	}

	/**
	 * @param $order OrderInterface
	 */
	private function subscribeToNewsletter($order)
	{
		try {
			$currentCustomerId = $order->getCustomerId();

			$currentCustomerId
				? $this->subscriber->subscribeCustomerById($currentCustomerId)
				: $this->subscriber->subscribe($order->getCustomerEmail());
		} catch (Exception $e) {
			$this->logger->error($e->getMessage());
		}
	}

    /**
     * @param $order
     */
	private function createCustomerAccount($order)
    {
        try {
            if ($order->getCustomerId()) return;

            $customer = $this->customerFactory->create();

            $customer->setWebsiteId($order->getStore()->getWebsiteId());
            $customer->setEmail($order->getCustomerEmail());
            $customer->setFirstname($order->getCustomerFirstname() ? $order->getCustomerFirstname() : $order->getBillingAddress()->getFirstname());
            $customer->setLastname($order->getCustomerLastname() ? $order->getCustomerLastname() : $order->getBillingAddress()->getLastname());

            $customer->save();

            $this->createCustomerAddress($customer, [$order->getBillingAddress(), $order->getShippingAddress()]);

            $this->orderHelper->assignCustomerToOrder($order);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @param $customer
     * @param $addresses
     *
     * @return void
     */
    private function createCustomerAddress($customer, $addresses)
    {
        try {
            foreach ($addresses as $address) {
                if (empty($address) || empty($address->getData())) continue;

                $customerAddress = $this->addressFactory->create();

                $customerAddress->setCustomerId($customer->getId())
                    ->setFirstname($address->getFirstname())
                    ->setLastname($address->getLastname())
                    ->setCountryId($address->getCountryId())
                    ->setRegion($address->getRegion())
                    ->setRegionId($address->getRegionId())
                    ->setPostcode($address->getPostcode())
                    ->setCity($address->getCity())
                    ->setTelephone($address->getTelephone())
                    ->setStreet($address->getStreet())
                    ->setSaveInAddressBook('1');

                $address->getAddressType() == 'billing'
                    ? $customerAddress->setIsDefaultBilling('1')
                    : $customerAddress->setIsDefaultShipping('1');

                $this->addressResource->save($customerAddress);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
