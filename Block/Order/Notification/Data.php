<?php

namespace IWD\CheckoutConnector\Block\Order\Notification;

use IWD\CheckoutConnector\Helper\PaymentMethod;
use Magento\Framework\App\Config\Initial;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\View\LayoutFactory;
use Magento\Payment\Model\Config;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\Factory;
use Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory;
use Magento\Store\Model\App\Emulation;
use IWD\CheckoutConnector\Block\Info\MultipleOffline;
use Psr\Log\LoggerInterface;

/**
 * Class Data
 *
 * @package IWD\CheckoutConnector\Block\Order\Notification
 */
class Data extends \Magento\Payment\Helper\Data
{
    private $customPaymentBlockHtml = [
        'iwd_checkout_pay',
        'iwd_checkout_paypal',
        'iwd_checkout_multiple_payment',
    ];

	/**
	 * @var PaymentMethod
	 */
    public $IWDPaymentMethodHelper;

	/**
	 * @var CollectionFactory
	 */
    public $paymentCollectionFactory;

	/**
	 * @var MultipleOffline
	 */
	public $multipleOffline;

	/**
	 * @var LoggerInterface
	 */
	public $logger;

	/**
	 * @param  Context  $context
	 * @param  LayoutFactory  $layoutFactory
	 * @param  Factory  $paymentMethodFactory
	 * @param  Emulation  $appEmulation
	 * @param  Config  $paymentConfig
	 * @param  Initial  $initialConfig
	 * @param  CollectionFactory  $paymentCollectionFactory
	 * @param  PaymentMethod  $IWDPaymentMethodHelper
	 */
	public function __construct(
		Context $context,
		LayoutFactory $layoutFactory,
		Factory $paymentMethodFactory,
		Emulation $appEmulation,
		Config $paymentConfig,
		Initial $initialConfig,
		CollectionFactory $paymentCollectionFactory,
		PaymentMethod $IWDPaymentMethodHelper,
		MultipleOffline $multipleOffline,
		LoggerInterface $logger
	) {
		parent::__construct($context, $layoutFactory, $paymentMethodFactory, $appEmulation, $paymentConfig, $initialConfig);

		$this->IWDPaymentMethodHelper   = $IWDPaymentMethodHelper;
		$this->paymentCollectionFactory = $paymentCollectionFactory;
		$this->multipleOffline = $multipleOffline;
		$this->logger = $logger;
	}

	/**
	 * @param  InfoInterface  $info
	 * @param $storeId
	 *
	 * @return string
	 * @throws \Exception
	 */
    public function getInfoBlockHtml(InfoInterface $info, $storeId)
    {
	    try {
		    $paymentCollection = $this->IWDPaymentMethodHelper->getCollection('entity_id', $info->getEntityId());

		    if ($paymentCollection->getSize()) {
			    $payment = $paymentCollection->getFirstItem();
			    $additionalInformation = $payment->getAdditionalInformation();

			    if(isset($additionalInformation['iwd_method_title']) && in_array($payment->getMethod(), $this->customPaymentBlockHtml)) {
				    $paymentBlockHtml = '<dl class="payment-method"><dt class="title">';
				    $paymentBlockHtml .= $additionalInformation['iwd_method_title'];

				    if ($payment->getMethod() === 'iwd_checkout_multiple_payment') {
					    $paymentBlockHtml .= $this->multipleOffline->getCustomPaymentBlockHtml($additionalInformation, $payment->getPoNumber());
				    }

					if (isset($additionalInformation['iwd_additional_info'])) {
						$paymentBlockHtml .= $this->IWDPaymentMethodHelper->getAdditionalNotificationInfoHtml($additionalInformation);
					}

				    $paymentBlockHtml .= '</dt></dl>';

				    return $paymentBlockHtml;
			    }
		    }
	    } catch (\Exception $e) {
		    $this->logger->error($e->getMessage());
	    }

	    return parent::getInfoBlockHtml($info, $storeId);
    }
}
