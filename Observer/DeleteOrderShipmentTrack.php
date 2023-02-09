<?php

namespace IWD\CheckoutConnector\Observer;

/**
 * Class DeleteOrderShipmentTrack
 *
 * @package IWD\CheckoutConnector\Observer
 */
class DeleteOrderShipmentTrack extends AbstractOrderShipmentTrack
{
	const STATUS = 'CANCELLED';

	/**
	 * @return string|void
	 */
	public function getTrackerStatus()
	{
		return self::STATUS;
	}
}