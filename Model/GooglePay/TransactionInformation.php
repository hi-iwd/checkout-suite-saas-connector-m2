<?php

namespace IWD\CheckoutConnector\Model\GooglePay;

use IWD\CheckoutConnector\Model\Address\ShippingMethods;
use IWD\CheckoutConnector\Model\Api\FormatData;
use IWD\CheckoutConnector\Model\Cart\CartItems;
use IWD\CheckoutConnector\Model\Cart\CartTotals;
use IWD\CheckoutConnector\Model\Quote\Quote;
use IWD\CheckoutConnector\Model\DeliveryStep;
use IWD\CheckoutConnector\Model\PaymentStep;
use Magento\Checkout\Api\TotalsInformationManagementInterface;
use Magento\Quote\Api\ShipmentEstimationInterface;
use IWD\CheckoutConnector\Api\TransactionInformationInterface;
use Magento\Directory\Model\RegionFactory;


class TransactionInformation implements TransactionInformationInterface
{


    /**
     * @var Quote
     */
    private $quote;
    /**
     * @var CartItems
     */
    private $cartItems;
    /**
     * @var CartTotals
     */
    private $cartTotals;
    /**
     * @var ShippingMethods
     */
    private $shippingMethods;
    /**
     * @var DeliveryStep
     */
    private $deliveryStep;
    /**
     * @var PaymentStep
     */
    private $paymentStep;
    /**
     * @var RegionFactory
     */
    private $regionFactory;
    /**
     * @var FormatData
     */
    private $formatData;

    /**
     * TransactionInformation constructor.
     * @param CartItems $cartItems
     * @param CartTotals $cartTotals
     * @param ShippingMethods $shippingMethods
     * @param DeliveryStep $deliveryStep
     * @param PaymentStep $paymentStep
     * @param Quote $quote
     * @param RegionFactory $regionFactory
     * @param FormatData $formatData
     */
    public function __construct(
        CartItems $cartItems,
        CartTotals $cartTotals,
        ShippingMethods $shippingMethods,
        DeliveryStep $deliveryStep,
        PaymentStep $paymentStep,
        Quote $quote,
        RegionFactory $regionFactory,
         FormatData $formatData
    )
    {
        $this->shippingMethods = $shippingMethods;
        $this->cartItems = $cartItems;
        $this->cartTotals = $cartTotals;
        $this->quote = $quote;
        $this->deliveryStep = $deliveryStep;
        $this->paymentStep = $paymentStep;
        $this->regionFactory = $regionFactory;
        $this->formatData = $formatData;
    }

    public function getShippingMethods($cartId)
    {

        $quote = $this->quote->getQuote($cartId);

        $methods = [];
        foreach ($this->shippingMethods->getShippingMethods($quote) as $method) {
            $methods[] = [
                "id" => $method['method_code'],
                "label" => $method["amount"] . ' ' . $method['carrier_title'],
                "description" => ""
            ];
        }

        return $methods;

    }

    /**
     * @param string $cartId
     * @param mixed $data
     * @return array|mixed
     * @throws \Exception
     */
    public function calculate($cartId, $data)
    {


        if (isset($data['shippingAddress']) && isset($data['shippingOptionData'])) {
            $shippingAddress = $data['shippingAddress'];
            $shippingData = $data['shippingOptionData'];

            $name = explode(" ", $shippingAddress['name'] ?? '');

            $region = $this->regionFactory->create();
            $regionId = $region->loadByCode($shippingAddress['administrativeArea'], $shippingAddress['countryCode'])->getId();

            $addres = [
                'first_name' => $name[0] ?? '',
                'last_name' => $name[1] ?? '',
                'address' => $shippingAddress['address1'] ?? '',
                'country' => $shippingAddress['countryCode'],
                'region_id' => $regionId,
                'state' => $shippingAddress['administrativeArea'],
                'city' => $shippingAddress['locality'],
                'postcode' => $shippingAddress['postalCode'],
                'phone' => $shippingAddress['phoneNumber'] ?? ''

            ];

            $address = [
                'email' => isset($data["email"]) ? $data["email"] : '',
                'shipping' => $addres,
                'billing' => $addres,
                'ship_bill_to_different_address' => 0,

            ];
            $quote = $this->quote->getQuote($cartId);
            $formattedAddressData = $this->formatData->format($address);
            $quote->getShippingAddress()->addData($formattedAddressData['shipping']);
            $quote->getBillingAddress()->addData($formattedAddressData['billing']);
            $quote->save();

            if ($shippingData['id'] != 'shipping_option_unselected') {
                $response = $this->paymentStep->prepareData($cartId, ['shipping_method' => $shippingData['id']]);
            } else {
                $shippingMethod = $this->shippingMethods->getShippingMethods($quote);
                $selectedShippingMethod = $shippingMethod[0];
                $response = $this->paymentStep->prepareData($cartId, ['shipping_method' => $selectedShippingMethod['method_code']]);
            }


            $result['info'] = [
                'newShippingOptionParameters' => [
                    "defaultSelectedOptionId" => isset($response['chosen_delivery_method']['method_code']) ? $response['chosen_delivery_method']['method_code'] : 'shipping_option_unselected' ,
                    "shippingOptions" => $this->getShippingMethods($cartId),

                ],
                'newTransactionInfo' =>
                    [
                        "displayItems" => [
                            [

                                "label" => "Subtotal",
                                "type" => "SUBTOTAL",
                                "price" => $response['cart']["subtotal"]
                            ],
                            [

                                "label" => "Tax",
                                "type" => "TAX",
                                "price" => $response['cart']["tax"]
                            ],
                            [
                                "type" => "LINE_ITEM",
                                "label" => "Shipping cost",
                                "price" => $response['cart']['shipping'],
                                "status" => "FINAL"
                            ],


                        ],
                        "currencyCode" => $response['cart']['quote_currency'],
                        "totalPriceStatus" => "FINAL",
                        "totalPrice" => $response['cart']['grand_total'],
                        "totalPriceLabel" => "Total"
                    ],
            ];

        }
        return $result;

    }

}