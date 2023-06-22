<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace IWD\CheckoutConnector\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Payment\Helper\Data;

/**
 * Class PaymentMethod
 */
class PaymentMethod extends Column
{
    /**
     * @var Data
     */
    protected $paymentHelper;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Data $paymentHelper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Data $paymentHelper,
        array $components = [],
        array $data = []
    ) {
        $this->paymentHelper = $paymentHelper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $options = $this->paymentHelper->getPaymentMethodList(true, true);

            foreach ($dataSource['data']['items'] as & $item) {
                if ($this->getData('name') == 'iwd_checkout_pay' && isset($item['iwd_checkout_pay'])) {
                    $item[$this->getData('name')] = $item['iwd_checkout_pay'];
                }
            }
        }

        return $dataSource;
    }
}
