<?php

namespace IWD\CheckoutConnector\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use IWD\CheckoutConnector\Model\Order\OrderUpdater;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class ChangeOrderStatus
 *
 * @package IWD\CheckoutConnector\Observer
 */
class ChangeOrderStatus implements ObserverInterface
{
    /**
     * @var OrderUpdater
     */
    private $orderUpdater;

    /**
     * ChangeOrderStatus constructor.
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
	    $this->orderUpdater->setOrder($observer->getEvent()->getOrder());
		$this->orderUpdater->update();
    }
}