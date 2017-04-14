<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */

namespace Paazl\Shipping\Setup;

use Paazl\Shipping\Setup\PaazlSetup;
use Paazl\Shipping\Setup\PaazlSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory
     *
     * @var PaazlSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init
     *
     * @param PaazlSetupFactory $eavSetupFactory
     */
    public function __construct(PaazlSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {

        /** @var PaazlSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $groupName = 'Paazl';
        $entityTypeId = $eavSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $attributeSetId = $eavSetup->getAttributeSetId($entityTypeId, 'Default');

        // Create group
        $eavSetup->addAttributeGroup(\Magento\Catalog\Model\Product::ENTITY, 'Default', 'Paazl', 62);

        foreach($eavSetup->getAttributeList() as $attributeInfo) {
            // Create attributes
            $attribute = $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                $attributeInfo['attributeCode'],
                [
                    'group' => 'Paazl',
                    'sort_order' => 40,
                    'type' => 'varchar',
                    'backend' => '',
                    'frontend' => '',
                    'label' => $attributeInfo['label'],
                    'input' => 'text',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_WEBSITE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'visible_in_advanced_search' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                ]
            );
        }
    }
}
