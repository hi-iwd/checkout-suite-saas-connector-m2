<?php

namespace IWD\CheckoutConnector\Model;

use IWD\CheckoutConnector\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface as Repository;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Api\Data\StoreInterface;


/**
 * Class ProductRepository
 * @package IWD\CheckoutConnector\Model
 */
class ProductRepository implements ProductRepositoryInterface
{
    /**
     * @var Repository
     */
    private $productRepository;
    /**
     * @var FilterBuilder
     */
    private $filterBuilder;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var StoreInterface
     */
    private $store;
    /**
     * @var FilterGroup
     */
    private $filterGroup;
    private $productCollection;

    /**
     * @param Repository $productRepository
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreInterface $store
     * @param FilterGroup $filterGroup
     */
    public function __construct(
        Repository            $productRepository,
        FilterBuilder         $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreInterface        $store,
        FilterGroup           $filterGroup,
        CollectionFactory     $productCollection
    )
    {
        $this->productRepository = $productRepository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->store = $store;
        $this->filterGroup = $filterGroup;
        $this->productCollection = $productCollection;
    }

    /**
     * @param string $searchCriteria
     * @return mixed[]|string
     * @api
     */
    public function getList($searchCriteria)
    {
        $productsData = [];

        $collection = $this->productCollection->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('visibility', array('in' => array(1, 2, 3, 4)))
            ->addAttributeToFilter(
                [
                    ['attribute' => 'name', 'like' => '%'.$searchCriteria.'%'],
                    ['attribute' => 'sku', 'like' => '%'.$searchCriteria.'%']
                ])
            ->addAttributeToFilter('type_id', ['eq' => 'simple'])->load();

        if ($collection) {
            foreach ($collection as $k => $product) {
                $productsData[$k] = [
                    'sku' => $product->getSku(),
                    'name' => $product->getName(),
                ];
            }
        }

        return $productsData;
    }
}


