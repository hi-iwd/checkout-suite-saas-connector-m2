<?php

namespace IWD\CheckoutConnector\Block;

use Magento\Checkout\Block\Onepage as CheckoutOnepage;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Data\Form\FormKey;
use Magento\Checkout\Model\CompositeConfigProvider;
use IWD\CheckoutConnector\Helper\Data;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * Class Frame
 *
 * @package IWD\CheckoutConnector\Block
 */
class Frame extends CheckoutOnepage
{
    const CMS_TYPE = 'Magento2';

    /**
     * @var CheckoutSession
     */
    public $checkoutSession;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var JsonHelper
     */
    private $jsonHelper;

    /**
     * Frame constructor.
     *
     * @param Context $context
     * @param FormKey $formKey
     * @param CompositeConfigProvider $configProvider
     * @param CheckoutSession $checkoutSession
     * @param CustomerSession $customerSession
     * @param Data $helper
     * @param Http $request
     * @param array $layoutProcessors
     * @param array $data
     */
    public function __construct(
        Context $context,
        FormKey $formKey,
        CompositeConfigProvider $configProvider,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        Data $helper,
        Http $request,
        JsonHelper $jsonHelper,
        array $layoutProcessors = [],
        array $data = []
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->helper = $helper;
        $this->request = $request;
        $this->jsonHelper = $jsonHelper;

        parent::__construct($context, $formKey, $configProvider, $layoutProcessors, $data);
    }

    /**
     * @return string
     */
    public function getCheckoutIframeId()
    {
        return $this->helper->getCheckoutIframeId();
    }

	/**
	 * @return string
	 * @throws LocalizedException
	 * @throws NoSuchEntityException
	 */
    public function getFrameUrl()
    {
	    $requestParams = $this->request->getParams();
	    $iframeParams  = $this->helper->getFrameParams($this->checkoutSession->getQuote());

	    if (isset($requestParams['paypal_order_id']) && $requestParams['paypal_order_id']) {
		    $iframeParams['paypal_order_id'] = $requestParams['paypal_order_id'];
	    }

	    if (isset($requestParams['paypal_funding_source']) && $requestParams['paypal_funding_source']) {
		    $iframeParams['paypal_funding_source'] = $requestParams['paypal_funding_source'];
	    }

	    if ($this->customerSession->isLoggedIn()) {
		    $iframeParams['customer_token'] = $this->helper->getCustomerToken();
		    $iframeParams['customer_email'] = $this->customerSession->getCustomer()->getEmail();
	    }

	    $iframeParams['customer_group'] = $this->getCustomerGroup();

	    return $this->helper->getCheckoutAppUrl().'?'.http_build_query($iframeParams);
    }

    /**
     * Get iframe config
     *
     * @return bool|false|string
     */
    public function getJsonConfig()
    {
        $config = $this->getIframeConfig();

        $implementationArray = [
            'IWD_CheckoutConnector/js/view/iwd_checkout' => $config
        ];

        return $this->jsonHelper->jsonEncode($implementationArray);
    }

    /**
     * @return array
     */
    private function getIframeConfig()
    {
        return [
            'checkoutIframeId' => $this->helper->getCheckoutIframeId(),
            'editCartUrl'      => $this->getEditCartUrl(),
            'loginUrl'         => $this->getLoginUrl(),
            'resetPasswordUrl' => $this->getResetPasswordUrl(),
            'successActionUrl' => $this->getSuccessActionUrl()
        ];
    }

	/**
	 * @return string
	 */
	public function getSuccessActionUrl()
	{
		return $this->getUrl('checkout_page/index/success');
	}

	/**
	 * @return string
	 */
	public function getEditCartUrl()
	{
		return $this->getUrl('checkout/cart');
	}

	/**
	 * @return string
	 */
	public function getLoginUrl()
	{
		return $this->getUrl('customer/ajax/login/');
	}

	/**
	 * @return string
	 */
	public function getResetPasswordUrl()
	{
		return $this->getUrl('customer/account/forgotpassword/');
	}

    /**
     * @return mixed
     */
    public function getCustomerGroup()
    {
        if($this->customerSession->isLoggedIn()) {
            return $this->customerSession->getCustomer()->getGroupId();
        }

        return '0';
    }
}