<?php

namespace IWD\CheckoutConnector\Model;

use Exception;
use IWD\CheckoutConnector\Api\Data\PaymentInterfaceFactory;
use IWD\CheckoutConnector\Api\InvoiceManagementInterface;
use IWD\CheckoutConnector\Api\OrderInterface;
use IWD\CheckoutConnector\Api\PaymentRepositoryInterface;
use IWD\CheckoutConnector\Helper\Order as OrderHelper;
use IWD\CheckoutConnector\Helper\OfflinePayments as OfflinePaymentsHelper;
use IWD\CheckoutConnector\Model\Address\Addresses;
use IWD\CheckoutConnector\Model\Address\ShippingMethods;
use IWD\CheckoutConnector\Model\Cart\CartItems;
use IWD\CheckoutConnector\Model\Cart\CartTotals;
use IWD\CheckoutConnector\Model\Quote\Quote;
use IWD\CheckoutConnector\Model\Ui\IWDCheckoutPayConfigProvider;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteManagement;
use Magento\Quote\Model\QuoteValidator;
use Magento\Sales\Api\Data\OrderStatusHistoryInterface;
use Magento\Sales\Api\OrderStatusHistoryRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Throwable;

/**
 * Class Order
 *
 * @package IWD\CheckoutConnector\Model
 */
class Order implements OrderInterface
{
    protected $orderCreationResult = [
        'error'        => 1,
        'order_status' => 'not_created'
    ];

    /**
     * @var QuoteManagement
     */
    private $quoteManagement;

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
     * @var OfflinePaymentsHelper
     */
    private $offlinePaymentsHelper;

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
     * @var Quote
     */
    private $quote;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CustomDataProvider
     */
    private $customDataProvider;

    /**
     * @var QuoteValidator
     */
    protected $quoteValidator;

    /**
     * Order constructor.
     *
     * @param QuoteManagement $quoteManagement
     * @param OrderFactory $orderFactory
     * @param AccessValidator $accessValidator
     * @param OrderSender $orderSender
     * @param Addresses $address
     * @param ShippingMethods $shippingMethods
     * @param CartItems $cartItems
     * @param CartTotals $cartTotals
     * @param OrderHelper $orderHelper
     * @param OfflinePaymentsHelper $offlinePaymentsHelper
     * @param InvoiceManagementInterface $invoiceManagement
     * @param OrderStatusHistoryRepositoryInterface $orderStatusHistoryRepository
     * @param SortOrderBuilder $sortOrderBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepositoryInterface $orderRepository
     * @param PaymentRepositoryInterface $paymentRepositoryInterface
     * @param PaymentInterfaceFactory $paymentInterface
     * @param IWDCheckoutPayConfigProvider $IWDCheckoutPayConfigProvider
     * @param Quote $quote
     * @param StoreManagerInterface $storeManager
     * @param CustomDataProvider $customDataProvider
     */
    public function __construct(
        QuoteManagement $quoteManagement,
        OrderFactory $orderFactory,
        AccessValidator $accessValidator,
        OrderSender $orderSender,
        Addresses $address,
        ShippingMethods $shippingMethods,
        CartItems $cartItems,
        CartTotals $cartTotals,
        OrderHelper $orderHelper,
        OfflinePaymentsHelper $offlinePaymentsHelper,
        InvoiceManagementInterface $invoiceManagement,
        OrderStatusHistoryRepositoryInterface $orderStatusHistoryRepository,
        SortOrderBuilder $sortOrderBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderRepositoryInterface $orderRepository,
        PaymentRepositoryInterface $paymentRepositoryInterface,
        PaymentInterfaceFactory $paymentInterface,
        IWDCheckoutPayConfigProvider $IWDCheckoutPayConfigProvider,
        Quote $quote,
        StoreManagerInterface $storeManager,
        CustomDataProvider $customDataProvider,
        QuoteValidator $quoteValidator
    ) {
        $this->quoteManagement = $quoteManagement;
        $this->orderFactory = $orderFactory;
        $this->accessValidator = $accessValidator;
        $this->orderSender = $orderSender;
        $this->address = $address;
        $this->shippingMethods = $shippingMethods;
        $this->cartItems = $cartItems;
        $this->cartTotals = $cartTotals;
        $this->orderHelper = $orderHelper;
        $this->offlinePaymentsHelper = $offlinePaymentsHelper;
        $this->invoiceManagement = $invoiceManagement;
        $this->orderStatusHistoryRepository = $orderStatusHistoryRepository;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->paymentRepositoryInterface = $paymentRepositoryInterface;
        $this->paymentInterface = $paymentInterface;
        $this->IWDCheckoutPayConfigProvider = $IWDCheckoutPayConfigProvider;
        $this->quote = $quote;
        $this->storeManager = $storeManager;
        $this->customDataProvider = $customDataProvider;
        $this->quoteValidator = $quoteValidator;
    }

    /**
     * @param string $quote_id
     * @param mixed $access_tokens
     * @param mixed $data
     * @return mixed[]|string
     * @throws Exception
     */
    public function create($quote_id, $access_tokens, $data) {
        if (!$this->accessValidator->checkAccess($access_tokens)) {
            return 'Permissions Denied!';
        }

        $paymentCode = $this->IWDCheckoutPayConfigProvider->getPaymentMethodCode();
        $paymentTitle = $data['payment_method_title'];

        try {
            $quote = $this->prepareQuoteForSubmit($quote_id, $data, $paymentCode, $paymentTitle);
            $this->processOrderCreation($quote, $data, $paymentTitle);
        } catch (Throwable $e) {
            $this->orderCreationResult['error'] = 1;
            $this->orderCreationResult['error_message'] = $e->getMessage();
        }

        return $this->orderCreationResult;
    }

    /**
     * @param string $quote_id
     * @param mixed $access_tokens
     * @param mixed $data
     * @return mixed[]|string
     * @throws Exception
     */
    public function offlineOrderCreate($quote_id, $access_tokens, $data) {
        if (!$this->accessValidator->checkAccess($access_tokens)) {
            return 'Permissions Denied!';
        }

        $offlineConfigProvider = $this->offlinePaymentsHelper->getConfigProvider($data['payment_method_code']);
        $paymentCode = $offlineConfigProvider->getPaymentMethodCode();
        $paymentTitle = $offlineConfigProvider->getTittle($data['payment_method_code']);
        $orderStatus = $offlineConfigProvider->getOrderStatus($data['payment_method_code']);

        try {
            $quote = $this->prepareQuoteForSubmit($quote_id, $data, $paymentCode, $paymentTitle);
            $this->processOrderCreation($quote, $data, $paymentTitle, $orderStatus);
        } catch (Throwable $e) {
            $this->orderCreationResult['error'] = 1;
            $this->orderCreationResult['error_message'] = $e->getMessage();
        }

        return $this->orderCreationResult;
    }

	/**
	 * @param $quote_id
	 * @param $data
	 * @param $paymentCode
	 * @param $paymentTitle
	 *
	 * @return \Magento\Quote\Model\Quote
	 * @throws LocalizedException
	 * @throws NoSuchEntityException
	 * @throws Exception
	 */
    protected function prepareQuoteForSubmit($quote_id, $data, $paymentCode, $paymentTitle) {
        $quote = $this->quote->getQuote($quote_id);

        // Set Currently selected Currency for Quote. Otherwise, Totals will be collected using Base Currency.
        $this->storeManager->getStore($quote->getStoreId())
            ->setCurrentCurrencyCode($quote->getQuoteCurrencyCode());

        $this->orderHelper->ignoreAddressValidation($quote);

        // Assign Guest Customer to Quote
        if (!$quote->getCustomer()->getId()) {
            $this->orderHelper->assignGuestCustomerToQuote($quote);
        }

        // Assign Customer Email to Quote if it is missing
        if(!$quote->getCustomer()->getEmail()){
            $quote->getCustomer()->setEmail($quote->getBillingAddress()->getEmail());
        }

        // Set Customer Email if needed
        if(!$quote->getCustomerEmail()){
            $quote->setCustomerEmail($quote->getCustomer()->getEmail());
        }

        // Set Payment Method
        $quote->getPayment()->setMethod($paymentCode);

        // Set Sales Order Payment Info
        $quote->getPayment()->importData(['method' => $paymentCode]);
        $quote->getPayment()->setAdditionalInformation([
            'iwd_method_code'  => isset($data['payment_method_code']) ? $data['payment_method_code'] : $paymentCode,
            'iwd_method_title' => $paymentTitle
        ]);

        // Set PO Number
        if($poNumber = $this->offlinePaymentsHelper->getPoNumber($paymentCode, $data)) {
            $quote->getPayment()->setPoNumber($poNumber);
        }

        // Save Custom Data to Quote
        if(isset($data['custom_data']['quote']) && $data['custom_data']['quote']) {
            $this->customDataProvider->saveDataToQuote($quote, $data['custom_data']['quote']);
        }

        // Collect Totals & Save Quote
	    $quote->collectTotals()->save();

        return $quote;
    }

	/**
	 * @param $quote
	 * @param $data
	 * @param $paymentTitle
	 * @param  null  $orderStatus
	 *
	 * @throws LocalizedException
	 * @throws Exception
	 */
    protected function processOrderCreation($quote, $data, $paymentTitle, $orderStatus = null) {
        // Order validation to avoid duplicate
        try {
            $order = $this->orderFactory->create()->loadByIncrementId($quote->getReservedOrderId());

            if($order->getId()) {
                $this->setOrderCreationResult($order, $quote);
                return;
            }
        } catch (\Exception $e) {
            // Do nothing, continue create order process
        }

	    // Create Order From Quote
	    $order = $this->quoteManagement->submit($quote);

	    if ($order->getEntityId()) {
            $this->setOrderCreationResult($order, $quote);

		    // Assign Customer
		    try {
			    $this->orderHelper->assignCustomerToOrder($order);
		    } catch (Throwable $e) {}

		    //Save Payment Information
		    $payment = $this->paymentInterface->create();
		    $payment->setPaymentMethod($paymentTitle);
		    $payment->setOrderId($order->getId());
		    $this->paymentRepositoryInterface->save($payment);

		    // Add Comments To Order
		    if (isset($data['comments']) && $data['comments']) {
			    foreach ($data['comments'] as $commentType => $commentVal) {
				    $order->addStatusHistoryComment(__($commentVal));
			    }
			    $order->save();
		    }

		    $order->getPayment()->setIsTransactionClosed(0);

		    // Set Transactions for Order
		    if (isset($data['payment_action']) && $data['payment_action'] && isset($data['transactions']) && $data['transactions']) {
			    $paymentAction = $data['payment_action'];
			    $transactions  = $data['transactions'];

			    if ($paymentAction == 'authorize') {
				    $this->orderHelper->addTransactionToOrder(
					    $order, $transactions['authorization'], Transaction::TYPE_AUTH, 'authorized'
				    );
			    } elseif ($paymentAction == 'auth_and_capture' || $paymentAction == 'capture') {
				    if ($paymentAction == 'auth_and_capture') {
					    $this->orderHelper->addTransactionToOrder(
						    $order, $transactions['authorization'], Transaction::TYPE_AUTH, 'authorized'
					    );
				    }

				    $this->orderHelper->addTransactionToOrder(
					    $order, $transactions['capture'], Transaction::TYPE_CAPTURE, 'captured'
				    );
				    $this->invoiceManagement->addInvoiceToOrder($order, $transactions['capture']['id']);
			    }
		    }

		    // Set Order Status
		    if ($orderStatus) {
			    $order->setStatus($orderStatus)->save();
		    }

		    $this->orderCreationResult['order_status'] = $order->getStatus();

		    // Save Custom Data to Order
		    if (isset($data['custom_data']['order']) && $data['custom_data']['order']) {
			    $this->customDataProvider->saveDataToOrder($order, $data['custom_data']['order']);
		    }
	    }
    }

    /**
     * @param mixed $access_tokens
     * @param mixed $data
     * @return mixed[]|string
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
            $transaction = isset($data['transaction']) && $data['transaction'] ? $data['transaction'] : NULL;

            if ($paymentAction == 'capture' && $transaction) {
                $order->getPayment()->setIsTransactionClosed(0);

                $this->orderHelper->addTransactionToOrder($order, $transaction['capture'], Transaction::TYPE_CAPTURE, 'captured');
                $this->invoiceManagement->addInvoiceToOrder($order, $transaction['capture']['id']);
            } elseif ($paymentAction == 'refund' && $transaction) {
                $this->orderHelper->addTransactionToOrder($order, $transaction['refund'], Transaction::TYPE_REFUND, 'refunded');
                $this->invoiceManagement->refundInvoiceByOrder($order, $transaction['refund']['id']);

                $this->removeComment($order->getId());
            } elseif ($paymentAction == 'void') {
                $order->getPayment()->setHasMessage(true);
                $order->getPayment()->setMessage('Voided authorization.');
                $order->getPayment()->registerVoidNotification();

                $this->orderRepository->save($order);
            } elseif ($paymentAction == 'hold') {
                if(isset($transaction['dispute']['id'])) {
                    $order->addCommentToStatusHistory('The Order was put on Hold because the Dispute with ID: '.$transaction['dispute']['id']
                        .' has been opened. Please access your Braintree account for more information.');
                } else {
                    $order->addCommentToStatusHistory('Order was put on Hold.');
                }
                $order->hold();

                $this->orderRepository->save($order);
            }

			$updatedOrder = $this->orderRepository->get($order->getId());

            $result = [
                'error' => 0,
                'order_status' => $updatedOrder->getStatus()
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
     * @param mixed $data
     * @return mixed[]|string
     * @throws Exception
     */
    public function getQuoteData($quote_id, $access_tokens, $data)
    {
        if (!$this->accessValidator->checkAccess($access_tokens)) {
            return 'Permissions Denied!';
        }

        $quote = $this->quote->getQuote($quote_id);

        // Set currently selected Currency for Quote. Otherwise Totals will be collected using Base Currency.
        $this->storeManager->getStore($quote->getStoreId())
            ->setCurrentCurrencyCode($quote->getQuoteCurrencyCode());

		if (isset($data['validate']) && $data['validate']) {
			try {
				$quote->getPayment()->setMethod(IWDCheckoutPayConfigProvider::CODE);
                $quote->getBillingAddress()->setShouldIgnoreValidation(true);
				$this->quoteValidator->validateBeforeSubmit($quote);
			} catch (Throwable $e) {
				return [
					'error' => 1,
					'error_message' => $e->getMessage()
				];
			}

			$quote->reserveOrderId();
			$orderIncrementId = $quote->getReservedOrderId();

			if ($orderIncrementId) {
				$this->quote->saveQuote($quote);
				$result['reserved_order_id'] = $orderIncrementId;
			}
		}

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

    /**
     * @param $order
     * @param $quote
     */
    private function setOrderCreationResult($order, $quote) {
        $this->orderCreationResult = [
            'error'              => 0,
            'order_id'           => $order->getId(),
            'order_increment_id' => $order->getIncrementId(),
            'order_status'       => $order->getStatus(),
            'quote_id'           => $quote->getId(),
        ];
    }
}
