<?php

namespace IWD\CheckoutConnector\Plugin\Framework\View\Element\UiComponent\DataProvider;

use Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory as MagentoCollectionFactory;
use \Magento\Framework\App\ResourceConnection;

/**
 * Class CollectionFactory
 * @package IWD\OrderGrid\Plugin\Framework\View\Element\UiComponent\DataProvider
 */
class CollectionFactory
{
    /**
     * @var
     */
    private $select;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var string[]
     */
    private $dataSource = [
        'sales_order_grid_data_source'            => '\Magento\Sales\Model\ResourceModel\Order\Grid\Collection',
        'sales_order_invoice_grid_data_source'    => '\Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult',
        'sales_order_shipment_grid_data_source'   => '\Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult',
        'sales_order_creditmemo_grid_data_source' => '\Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult'
    ];

    /**
     * CollectionFactory constructor.
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param MagentoCollectionFactory $subject
     * @param \Closure $proceed
     * @param $requestName
     * @return mixed
     */
    public function aroundGetReport(
        MagentoCollectionFactory $subject,
        \Closure $proceed,
        $requestName
    ) {
        $result = $proceed($requestName);

        if(isset($this->dataSource[$requestName])){
            if ($result instanceof $this->dataSource[$requestName]) {
                $this->select = $result->getSelect();
                $this->addPaymentMethodTitle($requestName);
                $this->select->group('main_table.entity_id');
            }
        }

        return $result;
    }

    /**
     * @param $dataSource
     */
    public function addPaymentMethodTitle($dataSource)
    {
        switch ($dataSource){
            case 'sales_order_grid_data_source':
                $this->select->joinLeft(
                    ['iwd_checkout_pay' => $this->resourceConnection->getTableName('iwd_checkout_pay')],
                    'main_table.entity_id = iwd_checkout_pay.order_id',
                    [
                        'iwd_checkout_pay' => 'iwd_checkout_pay.payment_method'
                    ]
                );
                break;
            case 'sales_order_creditmemo_grid_data_source':
            case 'sales_order_shipment_grid_data_source':
            case 'sales_order_invoice_grid_data_source':
                $this->select->joinLeft(
                    ['iwd_checkout_pay' => $this->resourceConnection->getTableName('iwd_checkout_pay')],
                    'main_table.order_id = iwd_checkout_pay.order_id',
                    [
                        'iwd_checkout_pay' => 'iwd_checkout_pay.payment_method'
                    ]
                );
                break;
        }
    }
}
