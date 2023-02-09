<?php

namespace IWD\CheckoutConnector\Observer;

/**
 * Class SaveOrderShipmentTrack
 *
 * @package IWD\CheckoutConnector\Observer
 */
class SaveOrderShipmentTrack extends AbstractOrderShipmentTrack
{
	const STATUS = 'SHIPPED';

	/**
	 * @return string|void
	 */
	public function getTrackerStatus()
	{
		return self::STATUS;
	}
}