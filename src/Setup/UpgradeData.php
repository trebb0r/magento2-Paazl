<?php
/**
 * Copyright (c) 2017 H&O E-commerce specialisten B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 */

namespace Paazl\Shipping\Setup;

use Paazl\Shipping\Setup\PaazlSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
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


    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.2.1') < 0) {
            /** @var PaazlSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->updateAttribute(\Magento\Catalog\Model\Product::ENTITY, 'matrix', 'backend_type', 'int');
            $eavSetup->updateAttribute(\Magento\Catalog\Model\Product::ENTITY, 'matrix', 'frontend_input', 'select');
            $eavSetup->updateAttribute(\Magento\Catalog\Model\Product::ENTITY, 'matrix', 'source_model', 'Paazl\Shipping\Model\Attribute\Source\Matrix');
        }

        $setup->endSetup();
    }
}