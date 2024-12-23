<?php

namespace IWD\CheckoutConnector\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

/**
 * Class UpgradeData
 * @package Magento\TestSetupDeclarationModule3\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory
    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface   $context
    )
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '4.7.0', '<')) {
            /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);


            // Add the product attribute UPC Type
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'dominate_upc_type',
                [
                    'type' => 'varchar',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'UPC Type',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
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
                \Magento\Catalog\Model\Product::ENTITY,
                'dominate_upc_code',
                [
                    'type' => 'varchar',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'UPC Code',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
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

            // Get all attribute set IDs
            $attributeSetIds = $eavSetup->getAllAttributeSetIds(\Magento\Catalog\Model\Product::ENTITY);

            foreach ($attributeSetIds as $attributeSetId) {
                // Get default attribute group ID for the attribute set
                $attributeGroupId = $eavSetup->getDefaultAttributeGroupId(
                    \Magento\Catalog\Model\Product::ENTITY,
                    $attributeSetId
                );

                // Add attributes to attribute set and group
                $eavSetup->addAttributeToSet(
                    \Magento\Catalog\Model\Product::ENTITY,
                    $attributeSetId,
                    $attributeGroupId,
                    'dominate_upc_type'
                );
                $eavSetup->addAttributeToSet(
                    \Magento\Catalog\Model\Product::ENTITY,
                    $attributeSetId,
                    $attributeGroupId,
                    'dominate_upc_code'
                );
            }
        }

        $setup->endSetup();
    }
}