<?php

namespace IWD\CheckoutConnector\Observer;

use IWD\CheckoutConnector\Model\Order\OrderUpdater;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Shipment\Track;
use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
use Magento\Catalog\Helper\ImageFactory;

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
     * @var ProductRepositoryInterfaceFactory
     */
    protected $productRepository;

    /**
     * @var ImageFactory
     */
    protected $productImageHelper;

    /**
     * SaveOrderShipmentTrack constructor.
     * @param OrderUpdater $orderUpdater
     * @param ProductRepositoryInterfaceFactory $productRepository
     * @param ImageFactory $productImageHelper
     */
    public function __construct(
        OrderUpdater                      $orderUpdater,
        ProductRepositoryInterfaceFactory $productRepository,
        ImageFactory                      $productImageHelper
    )
    {
        $this->orderUpdater = $orderUpdater;
        $this->productRepository = $productRepository;
        $this->productImageHelper = $productImageHelper;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var Track $tracker */
        $tracker = $observer->getEvent()->getTrack();

        // Get the shipment from the track
        $shipment = $tracker->getShipment();

        // Get the order from the shipment
        $order = $shipment->getOrder();

        $items = $this->getItems($shipment);

        $this->orderUpdater->setShipmentTracker([
            'status' => $this->getTrackerStatus(),
            'tracking_number' => $tracker->getTrackNumber(),
            'carrier' => $tracker->getCarrierCode() === 'custom' ? $tracker->getTitle() : $tracker->getCarrierCode(),
            'items' => $items,
        ]);
        $this->orderUpdater->setOrder($order);
        $this->orderUpdater->update();
    }

    /**
     * @param $shipment
     * @return array
     */
    public function getItems($shipment)
    {
        $data = [];

        foreach ($shipment->getItems() as $item) {
            $productData = $this->productRepository->create()->getById($item->getProductId());

            $imageUrl = $this->productImageHelper->create()->init($productData, 'product_thumbnail_image')
                ->setImageFile($productData->getThumbnail())->getUrl();

            $dataItem = [
                "name" => $item->getName(),
                "quantity" => $item->getQty(),
                "sku" => $item->getSku(),
                "url" => $productData->getProductUrl(),
                "image_url" => $imageUrl
            ];

            if (!empty($productData->getDominateUpcType()) && !empty($productData->getDominateUpcCode())) {
                $dataItem["upc"] = [
                    "type" => $productData->getDominateUpcType(),
                    "code" => $productData->getDominateUpcCode()
                ];
            }

            $data[] = $dataItem;

        }

        return $data;
    }

    /**
     * @return string
     */
    abstract public function getTrackerStatus();
}
