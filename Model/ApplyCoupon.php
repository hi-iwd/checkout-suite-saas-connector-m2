<?php
namespace IWD\CheckoutConnector\Model;

use Exception;
use IWD\CheckoutConnector\Api\ApplyCouponInterface;
use IWD\CheckoutConnector\Model\Cart\CartItems;
use IWD\CheckoutConnector\Model\Cart\CartTotals;
use IWD\CheckoutConnector\Model\Quote\Quote;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ApplyCoupon
 * @package IWD\CheckoutConnector\Model
 */
class ApplyCoupon implements ApplyCouponInterface
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
     * @var Quote
     */
    private $quote;

    /**
     * @var AccessValidator
     */
    private $accessValidator;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * ApplyCoupon constructor.
     *
     * @param CartItems $cartItems
     * @param CartTotals $cartTotals
     * @param Quote $quote
     * @param AccessValidator $accessValidator
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CartItems $cartItems,
        CartTotals $cartTotals,
        Quote $quote,
        AccessValidator $accessValidator,
        StoreManagerInterface $storeManager
    ) {
        $this->cartItems = $cartItems;
        $this->cartTotals = $cartTotals;
        $this->quote = $quote;
        $this->accessValidator = $accessValidator;
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $quote_id
     * @param mixed $access_tokens
     * @param null $data
     * @return mixed[]|string
     */
    public function getData($quote_id, $access_tokens, $data = null)
    {
        if (!$this->accessValidator->checkAccess($access_tokens)) {
            return 'Permissions Denied!';
        }

        try {
            $response = $this->setCoupon($quote_id, $data);
        } catch (Exception $e) {
            $response = [
                'errors'  => true,
                'message' => $e->getMessage()
            ];
        }

        return $response;
    }

    /**
     * @param $quote_id
     * @param $data
     * @return mixed
     * @throws Exception
     */
    public function setCoupon($quote_id, $data)
    {
        $coupon = isset($data['coupon_code']) ? $data['coupon_code'] : '';
        $quote = $this->quote->getQuote($quote_id);

        // Set currently selected Currency for Quote. Otherwise Totals will be collected using Base Currency.
        $this->storeManager->getStore($quote->getStoreId())
            ->setCurrentCurrencyCode($quote->getQuoteCurrencyCode());

        $quote->setCouponCode($coupon);
        $quote->collectTotals()->save();

        $response['cart_items'] = $this->cartItems->getItems($quote);
        $response['cart']       = $this->cartTotals->getTotals($quote);

        if ($quote->getCouponCode() != $data['coupon_code']) {
            $response['error'] = 'The coupon code "' . $data['coupon_code'] . '" is not valid.';
        }

        return $response;
    }
}
