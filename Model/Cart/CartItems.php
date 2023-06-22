<?php

namespace IWD\CheckoutConnector\Model\Cart;

use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
use Magento\Catalog\Helper\ImageFactory;
use Magento\Directory\Model\Currency;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\BlockFactory;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Model\Config as TaxConfig;

/**
 * Class CartItems
 *
 * @package IWD\CheckoutConnector\Model\Cart
 */
class CartItems
{
    /**
     * @var ProductRepositoryInterfaceFactory
     */
    protected $productRepository;

    /**
     * @var ImageFactory
     */
    protected $productImageHelper;

    /**
     * @var Currency
     */
    private $currency;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Emulation
     */
    protected $_appEmulation;

    /**
     * @var BlockFactory
     */
    protected $_blockFactory;

    /**
     * @var TaxConfig
     */
    protected $taxConfig;

    /**
     * CartItems constructor.
     * @param ProductRepositoryInterfaceFactory $productRepository
     * @param ImageFactory $productImageHelper
     * @param Currency $currency
     * @param StoreManagerInterface $storeManager
     * @param BlockFactory $blockFactory
     * @param Emulation $appEmulation
     * @param TaxConfig $taxConfig
     */
    public function __construct(
        ProductRepositoryInterfaceFactory $productRepository,
        ImageFactory $productImageHelper,
        Currency $currency,
        StoreManagerInterface $storeManager,
        BlockFactory $blockFactory,
        Emulation $appEmulation,
        TaxConfig $taxConfig

    ) {
        $this->productRepository = $productRepository;
        $this->productImageHelper = $productImageHelper;
        $this->currency = $currency;
        $this->_storeManager = $storeManager;
        $this->_blockFactory = $blockFactory;
        $this->_appEmulation = $appEmulation;
        $this->taxConfig = $taxConfig;
    }

    /**
     * @param $quote Quote
     * @return array
     */
    public function getItems($quote)
    {
        $data = [];
        $this->currency->load($quote->getQuoteCurrencyCode());

        foreach ($quote->getAllVisibleItems() as $index => $item) {
            $productData = $this->productRepository->create()->getById($item->getProductId());

	        try {
		        $imageUrl = $this->getImageUrl($productData, 'product_page_image_medium');
	        } catch (\Exception $e) {
		        $imageUrl = $this->productImageHelper->create()->init($productData, 'product_thumbnail_image')
		                                             ->setImageFile($productData->getThumbnail())->getUrl();
	        }

	        $data[] = [
                "name"    => $item->getName(),
                "sku"     => $item->getSku(),
                "price"   => number_format($this->getItemPrice($item), 2, '.', ''),
                "qty"     => $item->getQty(),
                "item_id" => $item->getProductId(),
                "type"    => $item->getProductType(),
                "image"   => $imageUrl,
                "options" => $this->getOptions($item),
            ];
        }

        return $data;
    }

	/**
	 * @param $item Quote\Item
	 *
	 * @return mixed
	 */
	protected function getItemPrice($item)
	{
		if ($this->taxConfig->displayCartPricesInclTax($item->getStoreId())
		    || $this->taxConfig->displayCartPricesBoth($item->getStoreId())) {
			return $item->getPriceInclTax();
		}

		return $item->getConvertedPrice();
	}

    /**
     * @param $product
     * @param string $imageType
     * @return mixed
     * @throws NoSuchEntityException
     */
    protected function getImageUrl($product, string $imageType = '')
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $this->_appEmulation->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND, true);
        $imageBlock = $this->_blockFactory->createBlock('Magento\Catalog\Block\Product\ListProduct');
        $productImage = $imageBlock->getImage($product, $imageType);
        $imageUrl = $productImage->getImageUrl();
        $this->_appEmulation->stopEnvironmentEmulation();

        return $imageUrl;
    }

	/**
	 * @param $item Quote\Item
	 *
	 * @return array
	 */
    public function getOptions($item)
    {
        $product_options = [];
	    $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());

	    if ($this->taxConfig->displayCartPricesBoth($item->getStoreId())) {
		    $product_options[] = [
			    'label' => 'text.excl_tax',
			    'value' => $this->currency->format($item->getConvertedPrice(), [], false)
		    ];
	    }

        if (!empty($options['attributes_info'])) {
            foreach ($options['attributes_info'] as $option) {
                $product_options[] = [
                    'label' => $option['label'],
                    'value' => $option['value'],
                ];
            }
        }

        if (!empty($options['bundle_options'])) {
            foreach ($options['bundle_options'] as $option) {
                $value = '';

                foreach ($option['value'] as $item) {
                    $value .= '(' . $item['qty'] . ') ' . $item['title'] . ' ' . $this->currency->format($item['price'], [], false);
                }

                $product_options[] = [
                    'label' => $option['label'],
                    'value' => $value,
                ];
            }
        }

        return $product_options;
    }
}
