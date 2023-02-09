<?php

namespace IWD\CheckoutConnector\Controller\Index;

use IWD\CheckoutConnector\Controller\Action;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Success
 *
 * @package IWD\CheckoutConnector\Controller\Index
 */
class Success extends Action
{
	const SUCCESS_PAGE_PATH = 'checkout/onepage/success';
	const CART_PAGE_PATH = 'checkout/cart/';

    /**
     * @return Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $params = $this->getRequest()->getParams();

        if (isset($params['quote_id']) && $params['quote_id']
            && isset($params['order_increment_id']) && $params['order_increment_id']
            && isset($params['order_status']) && $params['order_status'])
        {
            $this->checkoutSession->setLastQuoteId($params['quote_id']);
            $this->checkoutSession->setLastSuccessQuoteId($params['quote_id']);
            $this->checkoutSession->clearHelperData();

	        $orderId     = isset($params['order_id']) && $params['order_id'] ? $params['order_id'] : null;
	        $orderStatus = $params['order_status'];

	        if (!$orderId) {
		        $orderModel = $this->orderFactory->create();

		        // If order_id is not received, wait fot 2 seconds (5x times max) refresh model and recheck again.
		        for ($i = 1; $i <= 5; $i++) {
			        sleep(2);

			        try {
				        $order = $orderModel->loadByIncrementId($params['order_increment_id']);

				        if ($order->getId()) {
					        $orderId     = $order->getId();
					        $orderStatus = $order->getStatus();
					        break;
				        }
			        } catch (NoSuchEntityException $e) {/* Wait for the Order Entity to appear in the next loop.*/}
		        }
	        }

	        $this->checkoutSession->setLastOrderId($orderId);
	        $this->checkoutSession->setLastRealOrderId($params['order_increment_id']);
	        $this->checkoutSession->setLastOrderStatus($orderStatus);

            $resultRedirect->setPath($orderId ? self::SUCCESS_PAGE_PATH : self::CART_PAGE_PATH);
        } else {
            $resultRedirect->setPath(self::CART_PAGE_PATH);
        }

        return $resultRedirect;
    }
}
