<?php
namespace IWD\CheckoutConnector\Model;

use Exception;
use IWD\CheckoutConnector\Api\PaymentStepInterface;
use IWD\CheckoutConnector\Model\Address\Addresses;
use IWD\CheckoutConnector\Model\Address\ShippingMethods;
use IWD\CheckoutConnector\Model\Cart\CartItems;
use IWD\CheckoutConnector\Model\Cart\CartTotals;
use IWD\CheckoutConnector\Model\Quote\Quote;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class PaymentStep
 *
 * @package IWD\CheckoutConnector\Model
 */
class PaymentStep implements PaymentStepInterface
{
    /**
     * @var Cart\CartItems
     */
    private $cartItems;

    /**
     * @var Cart\CartTotals
     */
    private $cartTotals;

    /**
     * @var Address\ShippingMethods
     */
    private $shippingMethods;

    /**
     * @var Address\Addresses
     */
    private $address;

    /**
     * @var AccessValidator
     */
    private $accessValidator;

    /**
     * @var Quote
     */
    private $quote;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * PaymentStep constructor.
     *
     * @param CartItems $cartItems
     * @param CartTotals $cartTotals
     * @param ShippingMethods $shippingMethods
     * @param Addresses $address
     * @param AccessValidator $accessValidator
     * @param Quote $quote
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CartItems $cartItems,
        CartTotals $cartTotals,
        ShippingMethods $shippingMethods,
        Addresses $address,
        AccessValidator $accessValidator,
        Quote $quote,
        StoreManagerInterface $storeManager
    ) {
        $this->cartItems = $cartItems;
        $this->cartTotals = $cartTotals;
        $this->shippingMethods = $shippingMethods;
        $this->address = $address;
        $this->accessValidator = $accessValidator;
        $this->quote = $quote;
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $quote_id
     * @param mixed $access_tokens
     * @param null $data
     * @return array|\IWD\CheckoutConnector\Api\array_iwd|mixed|string
     */
    public function getData($quote_id, $access_tokens, $data = null)
    {
        if(!$this->accessValidator->checkAccess($access_tokens)) {
            return 'Permissions Denied!';
        }

        try {
            $response = $this->prepareData($quote_id, $data);
        } catch (Exception $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
        }

        return $response;
    }

    /**
     * @param $quote_id
     * @param $data
     * @return mixed
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function prepareData($quote_id, $data)
    {
        $quote = $this->quote->getQuote($quote_id);

        // Set currently selected Currency for Quote. Otherwise Totals will be collected using Base Currency.
        $this->storeManager->getStore($quote->getStoreId())
            ->setCurrentCurrencyCode($quote->getQuoteCurrencyCode());

        // Set Shipping Method for Quote if it exists in Request
        if(isset($data['shipping_method']) && $data['shipping_method']) {
            $quote->getShippingAddress()->setShippingMethod($data['shipping_method'])
                ->setCollectShippingRates(true);

            $quote->collectTotals();
            $quote->save();
        }

        $response['addresses']              = $this->address->formatAddress($quote);
        $response['chosen_delivery_method'] = $this->shippingMethods->getSelectedShippingMethod($quote);
        $response['cart_items']             = $this->cartItems->getItems($quote);
        $response['cart']                   = $this->cartTotals->getTotals($quote);

        return $response;
    }
}