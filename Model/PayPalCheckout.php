<?php
namespace IWD\CheckoutConnector\Model;

use IWD\CheckoutConnector\Api\PayPalCheckoutInterface;
use IWD\CheckoutConnector\Model\Address\Addresses;
use IWD\CheckoutConnector\Model\Address\ShippingMethods;
use IWD\CheckoutConnector\Model\Api\FormatData;
use IWD\CheckoutConnector\Model\Cart\CartItems;
use IWD\CheckoutConnector\Model\Cart\CartTotals;
use IWD\CheckoutConnector\Model\Quote\Quote;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class DeliveryStep
 *
 * @package IWD\CheckoutConnector\Model
 */
class PayPalCheckout implements PayPalCheckoutInterface
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
     * @var Api\FormatData
     */
    private $formatData;

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
     * DeliveryStep constructor.
     *
     * @param CartItems $cartItems
     * @param CartTotals $cartTotals
     * @param ShippingMethods $shippingMethods
     * @param Addresses $address
     * @param FormatData $formatData
     * @param AccessValidator $accessValidator
     * @param Quote $quote
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CartItems $cartItems,
        CartTotals $cartTotals,
        ShippingMethods $shippingMethods,
        Addresses $address,
        FormatData $formatData,
        AccessValidator $accessValidator,
        Quote $quote,
        StoreManagerInterface $storeManager
    ) {
        $this->cartItems = $cartItems;
        $this->cartTotals = $cartTotals;
        $this->shippingMethods = $shippingMethods;
        $this->address = $address;
        $this->formatData = $formatData;
        $this->accessValidator = $accessValidator;
        $this->quote = $quote;
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $quote_id
     * @param mixed $access_tokens
     * @param mixed $data
     * @return mixed[]|string
     */
    public function getData($quote_id, $access_tokens, $data = null)
    {
        if(!$this->accessValidator->checkAccess($access_tokens)) {
            return 'Permissions Denied!';
        }

        try {
            $response = $this->prepareData($quote_id, $data);
        } catch (\Exception $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage()
            ];
        }

        return $response;
    }

    /**
     * @param $quote_id
     * @param null $data
     * @return mixed
     * @throws \Exception
     */
    public function prepareData($quote_id, $data = null)
    {
        $quote = $this->quote->getQuote($quote_id);

        // Set currently selected Currency for Quote. Otherwise Totals will be collected using Base Currency.
        $this->storeManager->getStore($quote->getStoreId())
            ->setCurrentCurrencyCode($quote->getQuoteCurrencyCode());

        $formattedAddressData = $this->formatData->format($data);

        // Set Customer Address for Quote
        if ($formattedAddressData) {
            $quote->getShippingAddress()->addData($formattedAddressData['shipping']);
            $quote->getBillingAddress()->addData($formattedAddressData['billing']);
        }

        // Pass Shipping Methods in response if Quote is not Virtual and if Shipping Address is set.
        if(!$quote->getIsVirtual() && $quote->getShippingAddress()->getCountryId()) {
            $shippingMethods = $this->shippingMethods->getShippingMethods($quote);

            if(!empty($shippingMethods)) {
                $selectedShippingMethod = $this->shippingMethods->getSelectedShippingMethod($quote);

                // If Shipping Method is not selected yet by the customer, select the first available one.
                if(empty($selectedShippingMethod)
                    || !$this->shippingMethods->isSelectedShippingMethodAvailable($shippingMethods, $selectedShippingMethod)) {
                    $selectedShippingMethod = $shippingMethods[0];

                    $quote->getShippingAddress()->setShippingMethod($selectedShippingMethod['method_code'])
                        ->setCollectShippingRates(true);

                    $quote->collectTotals();
                }

                $response['delivery_methods']       = $shippingMethods;
                $response['chosen_delivery_method'] = $selectedShippingMethod;
            }
        }

        $quote->save();

        $response['addresses']  = $this->address->formatAddress($quote);
        $response['cart_items'] = $this->cartItems->getItems($quote);
        $response['cart']       = $this->cartTotals->getTotals($quote);

        return $response;
    }
}