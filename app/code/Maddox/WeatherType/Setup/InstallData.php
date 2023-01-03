<?php

declare(strict_types=1);

namespace Maddox\WeatherType\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class InstallData implements InstallDataInterface
{
    /**
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        private readonly EavSetupFactory $eavSetupFactory
    ) {}

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context): void
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            Product::ENTITY,
            'weather_type',
            [
                'group' => 'General',
                'type' => 'varchar',
                'label' => 'Weather Type',
                'input' => 'multiselect',
                'source' => 'Maddox\WeatherType\Model\Attribute\Source\Weather',
                'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'required' => false,
                'sort_order' => 19,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => true,
                'visible' => true,
                'is_html_allowed_on_front' => true,
                'visible_on_front' => true,
                'unique' => false,
                'used_in_product_listing' => true,
                'comparable' => true,
                'filterable' => true,
                'searchable' => true,
            ]
        );
    }
}
