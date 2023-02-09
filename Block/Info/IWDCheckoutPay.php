<?php

namespace IWD\CheckoutConnector\Block\Info;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Block\Info;

/**
 * Class IWDCheckoutPay
 *
 * @package IWD\CheckoutConnector\Block\Info
 */
class IWDCheckoutPay extends Info
{
	/**
	 * Get some specific information in format of array($label => $value)
	 *
	 * @return array
	 * @throws LocalizedException
	 */
	public function getSpecificInformation()
	{
		return $this->getInfo()->getOrder()->getPayment()->getAdditionalInformation('iwd_additional_info');
	}
}