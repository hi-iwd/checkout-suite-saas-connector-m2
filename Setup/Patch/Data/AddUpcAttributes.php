<?php

namespace IWD\CheckoutConnector\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

/**
 * Class AddUpcAttributes
 *
 * This class is a data patch implementation that adds custom attributes to Magento products:
 * 'dominate_upc_type' and 'dominate_upc_code'. These attributes are configurable and are added
 * to all attribute sets as part of the default setup process.
 *
 * Implements the DataPatchInterface to conform to Magento's patching system for data updates.
 */
class AddUpcAttributes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Apply data patch
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        // Add the product attribute UPC Type
        $eavSetup->addAttribute(
            Product::ENTITY,
            'dominate_upc_type',
            [
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'UPC Type',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => ''
            ]
        );

        // Add the product attribute UPC Code
        $eavSetup->addAttribute(
            Product::ENTITY,
            'dominate_upc_code',
            [
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'UPC Code',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'apply_to' => ''
            ]
        );

        // Add to attribute sets
        $attributeSetIds = $eavSetup->getAllAttributeSetIds(Product::ENTITY);

        foreach ($attributeSetIds as $attributeSetId) {
            $attributeGroupId = $eavSetup->getDefaultAttributeGroupId(
                Product::ENTITY,
                $attributeSetId
            );
            $eavSetup->addAttributeToSet(Product::ENTITY, $attributeSetId, $attributeGroupId, 'dominate_upc_type');
            $eavSetup->addAttributeToSet(Product::ENTITY, $attributeSetId, $attributeGroupId, 'dominate_upc_code');
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return string[]
     */
    public function getAliases()
    {
        return [];
    }
}