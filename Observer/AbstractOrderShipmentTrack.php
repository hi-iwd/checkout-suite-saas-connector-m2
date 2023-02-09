<?php

namespace IWD\CheckoutConnector\Observer;

use IWD\CheckoutConnector\Model\Order\OrderUpdater;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Shipment\Track;

/**
 * Class AbstractOrderShipmentTrack
 */
abstract class AbstractOrderShipmentTrack implements ObserverInterface
{
	/**
	 * @var OrderUpdater
	 */
	private $orderUpdater;

	/**
	 * SaveOrderShipmentTrack constructor.
	 *
	 * @param OrderUpdater $orderUpdater
	 */
	public function __construct(
		OrderUpdater $orderUpdater
	) {
		$this->orderUpdater = $orderUpdater;
	}

	/**
	 * @param  Observer  $observer
	 *
	 * @return void
	 * @throws LocalizedException
	 */
	public function execute(Observer $observer)
	{
		/** @var Track $tracker */
		$tracker = $observer->getEvent()->getTrack();

		$this->orderUpdater->setShipmentTracker([
			'status'          => $this->getTrackerStatus(),
			'tracking_number' => $tracker->getTrackNumber(),
			'carrier'         => $tracker->getCarrierCode() === 'custom' ? $tracker->getTitle() : $tracker->getCarrierCode(),
		]);
		$this->orderUpdater->setOrder($tracker->getShipment()->getOrder());
		$this->orderUpdater->update();
	}

	/**
	 * @return string
	 */
	abstract public function getTrackerStatus();
}
