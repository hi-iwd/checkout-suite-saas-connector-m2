<?php

namespace IWD\CheckoutConnector\Model;

use Exception;
use IWD\CheckoutConnector\Api\Data\PaymentInterfaceFactory;
use IWD\CheckoutConnector\Api\InvoiceManagementInterface;
use IWD\CheckoutConnector\Api\OrderInterface;
use IWD\CheckoutConnector\Api\PaymentRepositoryInterface;
use IWD\CheckoutConnector\Helper\Order as OrderHelper;
use IWD\CheckoutConnector\Model\Address\Addresses;
use IWD\CheckoutConnector\Model\Address\ShippingMethods;
use IWD\CheckoutConnector\Model\Cart\CartItems;
use IWD\CheckoutConnector\Model\Cart\CartTotals;
use IWD\CheckoutConnector\Model\Quote\Quote;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutPayConfigProvider;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutOfflinePayCheckmoConfigProvider;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutOfflinePayZeroConfigProvider;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Order
 *
 * @package IWD\CheckoutConnector\Model
 */
class Order implements OrderInterface
{
    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var QuoteManagement
     */
    private $quoteManagement;

    /**
     * @var MagentoOrder
     */
    private $order;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var AccessValidator
     */
    private $accessValidator;

    /**
     * @var OrderSender
     */
    private $orderSender;

    /**
     * @var Addresses
     */
    private $address;

    /**
     * @var ShippingMethods
     */
    private $shippingMethods;

    /**
     * @var CartItems
     */
    private $cartItems;

    /**
     * @var CartTotals
     */
    private $cartTotals;

    /**
     * @var OrderHelper
     */
    private $orderHelper;
    /**
     * @var InvoiceManagementInterface
     */
    private $invoiceManagement;
    /**
     * @var OrderStatusHistoryRepositoryInterface
     */
    private $orderStatusHistoryRepository;
    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var PaymentRepositoryInterface
     */
    private $paymentRepositoryInterface;

    /**
     * @var PaymentInterface
     */
    private $paymentInterface;

    /**
     * @var IWDCheckoutPayConfigProvider
     */
    private $IWDCheckoutPayConfigProvider;

    /**
     * @var IWDCheckoutOfflinePayCheckmoConfigProvider
     */
    private $IWDCheckoutOfflinePayCheckmoConfigProvider;

    /**
     * @var IWDCheckoutOfflinePayZeroConfigProvider
     */
    private $IWDCheckoutOfflinePayZeroConfigProvider;

    /**
     * @var Quote
     */
    private $quote;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Order constructor.
     *
     * @param QuoteFactory $quoteFactory
     * @param QuoteManagement $quoteManagement
     * @param OrderFactory $orderFactory
     * @param AccessValidator $accessValidator
     * @param OrderSender $orderSender
     * @param Addresses $address
     * @param ShippingMethods $shippingMethods
     * @param CartItems $cartItems
     * @param CartTotals $cartTotals
     * @param OrderHelper $orderHelper
     * @param InvoiceManagementInterface $invoiceManagement
     * @param OrderStatusHistoryRepositoryInterface $orderStatusHistoryRepository
     * @param SortOrderBuilder $sortOrderBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param PaymentRepositoryInterface $paymentRepositoryInterface
     * @param PaymentInterfaceFactory $paymentInterface
     */
    public function __construct(
        QuoteFactory $quoteFactory,
        QuoteManagement $quoteManagement,
        OrderFactory $orderFactory,
        AccessValidator $accessValidator,
        OrderSender $orderSender,
        Addresses $address,
        ShippingMethods $shippingMethods,
        CartItems $cartItems,
        CartTotals $cartTotals,
        OrderHelper $orderHelper,
        InvoiceManagementInterface $invoiceManagement,
        OrderStatusHistoryRepositoryInterface $orderStatusHistoryRepository,
        SortOrderBuilder $sortOrderBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        PaymentRepositoryInterface $paymentRepositoryInterface,
        PaymentInterfaceFactory $paymentInterface,
        IWDCheckoutPayConfigProvider $IWDCheckoutPayConfigProvider,
        IWDCheckoutOfflinePayCheckmoConfigProvider $IWDCheckoutOfflinePayCheckmoConfigProvider,
        IWDCheckoutOfflinePayZeroConfigProvider $IWDCheckoutOfflinePayZeroConfigProvider,
        Quote $quote,
        StoreManagerInterface $storeManager
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->quoteManagement = $quoteManagement;
        $this->orderFactory = $orderFactory;
        $this->accessValidator = $accessValidator;
        $this->orderSender = $orderSender;
        $this->address = $address;
        $this->shippingMethods = $shippingMethods;
        $this->cartItems = $cartItems;
        $this->cartTotals = $cartTotals;
        $this->orderHelper = $orderHelper;
        $this->invoiceManagement = $invoiceManagement;
        $this->orderStatusHistoryRepository = $orderStatusHistoryRepository;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->paymentRepositoryInterface = $paymentRepositoryInterface;
        $this->paymentInterface = $paymentInterface;
        $this->IWDCheckoutPayConfigProvider = $IWDCheckoutPayConfigProvider;
        $this->IWDCheckoutOfflinePayCheckmoConfigProvider = $IWDCheckoutOfflinePayCheckmoConfigProvider;
        $this->IWDCheckoutOfflinePayZeroConfigProvider = $IWDCheckoutOfflinePayZeroConfigProvider;
        $this->quote = $quote;
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $quote_id
     * @param mixed $access_tokens
     * @param mixed $data
     * @return \IWD\CheckoutConnector\Api\array_iwd|string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function create($quote_id, $access_tokens, $data)
    {
        if (!$this->accessValidator->checkAccess($access_tokens)) {
            return 'Permissions Denied!';
        }

        $quote = $this->quote->getQuote($quote_id);

        // Set currently selected Currency for Quote. Otherwise Totals will be collected using Base Currency.
        $this->storeManager->getStore($quote->getStoreId())
            ->setCurrentCurrencyCode($quote->getQuoteCurrencyCode());

        $this->orderHelper->ignoreAddressValidation($quote);

        if (!$quote->getCustomer()->getId()) {
            $this->orderHelper->assignCustomerToQuote($quote);
        }

        //assign customer email to quote if it is missing
        if(!$quote->getCustomer()->getEmail()){
            $quote->getCustomer()->setEmail($quote->getBillingAddress()->getEmail());
        }

        //set customer email if needed
        if(!$quote->getCustomerEmail()){
            $quote->setCustomerEmail($quote->getCustomer()->getEmail());
        }

        // IWD Checkout Pay Collection
        $paymentMethodCode = $this->IWDCheckoutPayConfigProvider->getPaymentMethodCode();
        $payment = $this->paymentInterface->create();
        $payment->setPaymentMethod($data['payment_method_title']);

        // Set Payment Method
        $quote->setPaymentMethod($paymentMethodCode);
        $quote->save();

        // Set Sales Order Payment
        $quote->getPayment()->importData(['method' => $paymentMethodCode]);

        //Set Real Payment Method Title
        $quote->getPayment()->setAdditionalInformation(array('iwd_method_title' => $data['payment_method_title']));

        // Collect Totals & Save Quote
        $quote->collectTotals()->save();

        // Create Order From Quote
        $order = $this->quoteManagement->submit($quote);

        if ($order->getEntityId()) {
            //Save payment information
            $payment->setOrderId($order->getId());
            $this->paymentRepositoryInterface->save($payment);

            // Add Comments To Order
            if(isset($data['comments']) && $data['comments']) {
                foreach($data['comments'] as $commentType => $commentVal) {
                    $order->addStatusHistoryComment(__($commentVal));
                }
            }

            // Set Transactions for order
            $paymentAction = $data['payment_action'];
            $transactions = $data['transactions'];

            $order->getPayment()->setIsTransactionClosed(0);

            $order->getPayment()->setAdditionalInformation(array('iwd_method_title' => $data['payment_method_title']));

            if ($paymentAction == 'authorize') {
                $this->orderHelper->addTransactionToOrder($order, $transactions['authorization'], Transaction::TYPE_AUTH, 'authorized');
            } elseif ($paymentAction == 'auth_and_capture' || $paymentAction == 'capture') {
                if ($paymentAction == 'auth_and_capture') {
                    $this->orderHelper->addTransactionToOrder($order, $transactions['authorization'], Transaction::TYPE_AUTH, 'authorized');
                }
                $this->orderHelper->addTransactionToOrder($order, $transactions['capture'], Transaction::TYPE_CAPTURE, 'captured');
                $this->invoiceManagement->addInvoiceToOrder($order, $transactions['capture']['id']);
            }

            if (!$order->getEmailSent()) {
                // Send order confirmation email to customer.
                $this->orderSender->send($order);
            }

            $result = [
                'order_id' => $order->getId(),
                'order_increment_id' => $order->getIncrementId(),
                'order_status' => $order->getStatus(),
                'quote_id' => $quote->getId(),
            ];

        } else {
            $result = [
                'error' => 1,
                'order_status' => 'not_created'
            ];
        }

        return $result;
    }

    /**
     * @param string $quote_id
     * @param mixed $access_tokens
     * @param mixed $data
     * @return \IWD\CheckoutConnector\Api\array_iwd|string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function offlineOrderCreate($quote_id, $access_tokens, $data)
    {
        if (!$this->accessValidator->checkAccess($access_tokens)) {
            return 'Permissions Denied!';
        }

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/offlineOrderCreate.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($quote_id);
        $logger->info(print_r($access_tokens,1));
        $logger->info(print_r($data,1));

        $quote = $this->quote->getQuote($quote_id);

        // Set currently selected Currency for Quote. Otherwise Totals will be collected using Base Currency.
        $this->storeManager->getStore($quote->getStoreId())
            ->setCurrentCurrencyCode($quote->getQuoteCurrencyCode());

        $this->orderHelper->ignoreAddressValidation($quote);

        if (!$quote->getCustomer()->getId()) {
            $this->orderHelper->assignCustomerToQuote($quote);
        }

        //assign customer email to quote if it is missing
        if(!$quote->getCustomer()->getEmail()){
            $quote->getCustomer()->setEmail($quote->getBillingAddress()->getEmail());
        }

        //set customer email if needed
        if(!$quote->getCustomerEmail()){
            $quote->setCustomerEmail($quote->getCustomer()->getEmail());
        }

        // IWD Checkout Pay Collection

        switch ($data['payment_method_code']){
            case 'check_or_money_order':
                $paymentMethodCode = $this->IWDCheckoutOfflinePayCheckmoConfigProvider->getPaymentMethodCode();
                $paymentTitle = $this->IWDCheckoutOfflinePayCheckmoConfigProvider->getConfigData('title');
                $order_status = $this->IWDCheckoutOfflinePayCheckmoConfigProvider->getConfigData('order_status');
                break;
            case 'zero':
                $paymentMethodCode = $this->IWDCheckoutOfflinePayZeroConfigProvider->getPaymentMethodCode();
                $paymentTitle = $this->IWDCheckoutOfflinePayZeroConfigProvider->getConfigData('title');
                $order_status = $this->IWDCheckoutOfflinePayZeroConfigProvider->getConfigData('order_status');
                break;
        }

        $payment = $this->paymentInterface->create();
        $payment->setPaymentMethod($paymentTitle);

        $logger->info('$paymentMethodCode = ' . $paymentMethodCode);

        // Set Payment Method
        $quote->setPaymentMethod($paymentMethodCode);
        $quote->save();

        // Set Sales Order Payment
        $quote->getPayment()->importData(['method' => $paymentMethodCode]);

        //Set Real Payment Method Title
        //$quote->getPayment()->setAdditionalInformation(array('iwd_method_title' => $data['payment_method_title']));

        // Collect Totals & Save Quote
        $quote->collectTotals()->save();

        // Create Order From Quote
        $order = $this->quoteManagement->submit($quote);

        if ($order->getEntityId()) {
            //Save payment information
            $payment->setOrderId($order->getId());
            $this->paymentRepositoryInterface->save($payment);

            // Add Comments To Order
            if(isset($data['comments']) && $data['comments']) {
                foreach($data['comments'] as $commentType => $commentVal) {
                    $order->addStatusHistoryComment(__($commentVal));
                }
            }

            // Set Transactions for order
            $order->getPayment()->setIsTransactionClosed(0);
            //$order->getPayment()->setAdditionalInformation(array('iwd_method_title' => $data['payment_method_title']));

            if (!$order->getEmailSent()) {
                // Send order confirmation email to customer.
                $this->orderSender->send($order);
            }

            $order->setStatus($order_status)->save();


            $result = [
                'order_id' => $order->getId(),
                'order_increment_id' => $order->getIncrementId(),
                'order_status' => $order->getStatus(),
                'quote_id' => $quote->getId(),
            ];

        } else {
            $result = [
                'error' => 1,
                'order_status' => 'not_created'
            ];
        }

        return $result;
    }

    /**
     * @param mixed $access_tokens
     * @param mixed $data
     * @return \IWD\CheckoutConnector\Api\array_iwd|string
     */
    public function update($access_tokens, $data)
    {
        if (!$this->accessValidator->checkAccess($access_tokens)) {
            return 'Permissions Denied!';
        }

        $orderId = $data['order_id'];
        $order = $this->orderFactory->create()->loadByIncrementId($orderId);

        if ($order->getEntityId()) {
            //Set Transactions for order
            $paymentAction = $data['payment_action'];
            $transaction = $data['transaction'];

            if ($paymentAction == 'capture') {
                $order->getPayment()->setIsTransactionClosed(0);
                $this->orderHelper->addTransactionToOrder($order, $transaction, Transaction::TYPE_CAPTURE, 'captured');
                $this->invoiceManagement->addInvoiceToOrder($order, $transaction['capture']['id']);
            } elseif ($paymentAction == 'refund') {
                $this->orderHelper->addTransactionToOrder($order, $transaction, Transaction::TYPE_REFUND, 'refunded');
                $this->invoiceManagement->refundInvoiceByOrder($order, $transaction['refund']['id']);

                $this->removeComment($order->getId());
            } elseif ($paymentAction == 'void') {
                $order->getPayment()->setHasMessage(true);
                $order->getPayment()->setMessage('Voided authorization.');
                $order->getPayment()->registerVoidNotification();

                $this->orderRepository->save($order);
            }

            $result = [
                'error' => 0,
                'order_status' => $order->getStatus()
            ];
        } else {
            $result = [
                'error' => 1,
                'order_status' => 'not_found'
            ];
        }

        return $result;
    }

    /**
     * @param string $quote_id
     * @param mixed $access_tokens
     * @return \IWD\CheckoutConnector\Api\array_iwd|string
     * @throws Exception
     */
    public function getQuoteData($quote_id, $access_tokens)
    {
        if (!$this->accessValidator->checkAccess($access_tokens)) {
            return 'Permissions Denied!';
        }

        $quote = $this->quote->getQuote($quote_id);

        $result['addresses']              = $this->address->formatAddress($quote);
        $result['chosen_delivery_method'] = $this->shippingMethods->getSelectedShippingMethod($quote);
        $result['cart_items']             = $this->cartItems->getItems($quote);
        $result['cart']                   = $this->cartTotals->getTotals($quote);

        return $result;
    }

    /**
     * @param $orderId
     */
    private function removeComment($orderId)
    {
        $sortOrder = $this->sortOrderBuilder
            ->setField(OrderStatusHistoryInterface::ENTITY_ID)
            ->setDirection(SortOrder::SORT_DESC)
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(OrderStatusHistoryInterface::PARENT_ID, $orderId, 'eq')
            ->setSortOrders([$sortOrder])
            ->setPageSize(1)
            ->create();

        $comments = $this->orderStatusHistoryRepository->getList($searchCriteria)->getItems();
        if ($comments) {
            $comment = array_shift($comments);
            try {
                $this->orderStatusHistoryRepository->delete($comment);
            } catch (Exception $e) {}
        }
    }
}
