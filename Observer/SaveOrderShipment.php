<?php

namespace IWD\CheckoutConnector\Observer;

use IWD\CheckoutConnector\Model\Order\OrderUpdater;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Shipment;

/**
 * Class SaveOrderShipment
 *
 * @package IWD\CheckoutConnector\Observer
 */
class SaveOrderShipment implements ObserverInterface
{
	/**
	 * @var OrderUpdater
	 */
	private $orderUpdater;

	/**
	 * SaveOrderShipment constructor.
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
		/** @var $shipment Shipment */
	    $shipment = $observer->getEvent()->getShipment();

		if(!$shipment->getAllTracks()) {
			$this->orderUpdater->setShipmentTracker(['status' => 'SHIPPED']);
			$this->orderUpdater->setOrder($shipment->getOrder());
			$this->orderUpdater->update();
		}
    }
}