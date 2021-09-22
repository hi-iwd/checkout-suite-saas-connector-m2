<?php

namespace IWD\CheckoutConnector\Model;

use IWD\CheckoutConnector\Api\Data\PaymentInterfaceFactory;
use IWD\CheckoutConnector\Api\InvoiceManagementInterface;
use IWD\CheckoutConnector\Api\OrderManagementInterface;
use IWD\CheckoutConnector\Api\PaymentRepositoryInterface;
use IWD\CheckoutConnector\Helper\Order as OrderHelper;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class OrderManagment
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderManagement implements OrderManagementInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var CustomerFactory
     */
    private $customerFactory;
    /**
     * @var QuoteFactory
     */
    private $quote;
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var QuoteManagement
     */
    private $quoteManagement;
    /**
     * @var OrderSender
     */
    private $orderSender;
    /**
     * @var PaymentInterfaceFactory
     */
    private $paymentInterface;
    /**
     * @var PaymentRepositoryInterface
     */
    private $paymentRepository;
    /**
     * @var InvoiceManagementInterface
     */
    private $invoiceManagement;
    /**
     * @var OrderHelper
     */
    private $orderHelper;

    /**
     * @param StoreManagerInterface $storeManager
     * @param CustomerFactory $customerFactory
     * @param QuoteFactory $quoteFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param ProductRepositoryInterface $productRepository
     * @param QuoteManagement $quoteManagement
     * @param OrderSender $orderSender
     */
    public function __construct(
        StoreManagerInterface       $storeManager,
        CustomerFactory             $customerFactory,
        QuoteFactory                $quoteFactory,
        CustomerRepositoryInterface $customerRepository,
        ProductRepositoryInterface  $productRepository,
        QuoteManagement             $quoteManagement,
        OrderSender                 $orderSender,
        PaymentInterfaceFactory     $paymentInterface,
        PaymentRepositoryInterface  $paymentRepository,
        InvoiceManagementInterface  $invoiceManagement,
        OrderHelper                 $orderHelper
    ) {
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->quote = $quoteFactory;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->quoteManagement = $quoteManagement;
        $this->orderSender = $orderSender;
        $this->paymentInterface = $paymentInterface;
        $this->paymentRepository = $paymentRepository;
        $this->invoiceManagement = $invoiceManagement;
        $this->orderHelper = $orderHelper;
    }

    /**
     * Perform place order.
     *
     * @param mixed $order
     * @return array
     * @throws \Exception
     */
    public function place($order)
    {
        return $this->createOrder($order);
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createOrder($data)
    {
        $product = $this->productRepository->get($data['sku']);
        if ($product) {

            $fullName = $this->getName($data['subscriber']['name']);
            $orderInfo['email'] = $data['subscriber']['email_address'];
            $orderInfo['items'] = [
                [
                    'product_id' => $product->getId(),
                    'qty' => 1
                ]
            ];
            $addressLine2 = $data['subscriber']['shipping_address']['address']['address_line_2'] ?? '';

            $orderInfo['address'] = [
                'firstname' => $fullName[0],
                'lastname' => $fullName[1],
                'email' => $data['subscriber']['email_address'],
                'telephone' => '-',
                'street' => [
                    $data['subscriber']['shipping_address']['address']['address_line_1'],
                    $addressLine2
                ],
                'city' => $data['subscriber']['shipping_address']['address']['admin_area_2'],
                'region_code' => $data['subscriber']['shipping_address']['address']['admin_area_1'],
                'postcode' => $data['subscriber']['shipping_address']['address']['postal_code'],
                'country_id' => $data['subscriber']['shipping_address']['address']['country_code'],
            ];
            $store = $this->storeManager->getStore();
            $quote = $this->quote->create();
            $quote->setStore($store);
            $quote->setCurrency();
            $quote->setCustomerId(null)
                ->setCustomerEmail($orderInfo['email'])
                ->setCustomerIsGuest(true)
                ->setCustomerGroupId(\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID);

            //add items in quote
            foreach ($orderInfo['items'] as $item) {
                $product = $this->productRepository->getById($item['product_id']);
                $product->setTaxClassId(0);
                if (isset($item['super_attribute']) && !empty($item['super_attribute'])) {
                    /* for configurable product */
                    $buyRequest = new \Magento\Framework\DataObject($item);
                    $quote->addProduct($product, $buyRequest);
                    $quote->save();
                    $quoteItem = $quote->getItemByProduct($product);
                    $quoteItem->setOriginalCustomPrice($data['grand_total']);
                    $quoteItem->setCustomPrice($data['grand_total']);
                    $quoteItem->setIsSuperMode(true);
                    $quoteItem->setTaxAmount(0);
                    $quoteItem->save();
                } else {
                    /* for simple product */
                    $quote->addProduct($product, intval($item['qty']));
                    $quoteItem = $quote->getItemByProduct($product);
                    $quoteItem->setOriginalCustomPrice($data['grand_total']);
                    $quoteItem->setCustomPrice($data['grand_total']);
                    $quoteItem->setTaxAmount(0);
                    $quoteItem->setIsSuperMode(true);

                }
            }

            //Set Billing and shipping Address to quote
            $quote->getBillingAddress()->addData($orderInfo['address']);
            $quote->getShippingAddress()->addData($orderInfo['address']);
            $quote->getPayment()->setAdditionalInformation(array('iwd_method_title' => 'Subscription'));

            // set shipping method
            $shippingAddress = $quote->getShippingAddress();
            $shippingAddress->setCollectShippingRates(true)
                ->collectShippingRates()
                ->setShippingMethod('subscription_subscription'); //shipping method, please verify flat rate shipping must be enable
            $quote->getShippingAddress()->setShippingMethod('subscription_subscription');


            $payment = $this->paymentInterface->create();
            $payment->setPaymentMethod('Subscription');

            // Set Payment Method
            $quote->setPaymentMethod('iwd_checkout_pay');
            // Set Sales Order Payment
            $quote->setInventoryProcessed(false); //decrease item stock equal to qty
            $quote->save();

            // Set Sales Order Payment
            $quote->getPayment()->importData(['method' => 'iwd_checkout_pay']);

            //Set Real Payment Method Title
            $quote->getPayment()->setAdditionalInformation(array('iwd_method_title' => 'Subscription'));
            // Collect Quote Totals & Save
            $quote->collectTotals()->save();
            // Create Order From Quote Object
            $order = $this->quoteManagement->submit($quote);
            /* for send order email to customer email id */
            $this->orderSender->send($order);
            /* get order real id from order */
            $orderId = $order->getIncrementId();
            if ($orderId) {
                $payment->setOrderId($order->getId());
                $this->paymentRepository->save($payment);

                // Set Transactions for order
                $transactions['id'] = $data['transaction_id'];

                $order->getPayment()->setIsTransactionClosed(0);
                $order->getPayment()->setAdditionalInformation(array('iwd_method_title' => 'Subscription'));

                $this->orderHelper->addTransactionToOrder($order, $transactions, Transaction::TYPE_CAPTURE, 'captured');
                $this->invoiceManagement->addInvoiceToOrder($order, $transactions['id']);
                return [
                    'id' => $orderId,
                    'status' => $order->getState()
                ];
            }

        }
        throw new \Exception('Something was wrong');
    }

    /**
     * @param $name
     * @return array
     */
    private function splitName($name)
    {
        $name = trim($name);
        $lastName = "";
        $arr = explode(" ", $name);
        if (count($arr) > 1) {
            array_shift($arr);
            $lastName = implode(" ", $arr);
        }
        return [$arr[0], $lastName];
    }

    /**
     * @param $data
     * @return array
     */
    private function getName($data)
    {
        if(isset($data['full_name'])) {
            return $this->splitName($data['full_name']);
        } else {
            return [$data['given_name'], $data['surname']];
        }
    }
}
