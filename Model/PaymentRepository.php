<?php

namespace IWD\CheckoutConnector\Model;

use IWD\CheckoutConnector\Api\Data\PaymentInterfaceFactory;
use IWD\CheckoutConnector\Api\PaymentRepositoryInterface;
use IWD\CheckoutConnector\Model\ResourceModel\PaymentMethod as ResourcePaymentMethod;
use IWD\CheckoutConnector\Model\ResourceModel\PaymentMethod\CollectionFactory as PaymentCollectionFactory;
use Magento\Framework\Exception\CouldNotDeleteException;

/**
 * Class PaymentRepository
 *
 * @package IWD\CheckoutConnector\Model
 */
class PaymentRepository implements PaymentRepositoryInterface
{
    /**
     * @var ResourcePaymentMethod
     */
    private $resource;

    /**
     * @var PaymentCollectionFactory
     */
    private $paymentFactory;

    /**
     * @var PaymentInterfaceFactory
     */
    private $paymentInterfaceFactory;

    /**
     * PaymentRepository constructor.
     *
     * @param ResourcePaymentMethod $resource
     * @param PaymentCollectionFactory $paymentFactory
     * @param PaymentInterfaceFactory $paymentInterfaceFactory
     */
    public function __construct(
        ResourcePaymentMethod $resource,
        PaymentCollectionFactory $paymentFactory,
        PaymentInterfaceFactory $paymentInterfaceFactory
    ) {
        $this->resource = $resource;
        $this->paymentFactory = $paymentFactory;
        $this->paymentInterfaceFactory = $paymentInterfaceFactory;
    }

    /**
     * @param $entityId
     * @return mixed
     */
    public function getById($entityId)
    {
        $collection =  $this->paymentFactory->create();
        return $collection->getItemById($entityId);
    }

    /**
     * @param $entityId
     * @return bool|string
     */
    public function deleteById($entityId)
    {
        try {
            $payment = $this->getById($entityId);
            $this->delete($payment);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return true;
    }

    /**
     * @param $payment
     * @return bool|string
     */
    public function save($payment)
    {
        try {
            $this->resource->save($payment);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return true;
    }

    /**
     * @param $payment
     * @return bool
     */
    public function delete($payment)
    {
        try {
            $this->resource->delete($payment);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }

        return true;
    }
}