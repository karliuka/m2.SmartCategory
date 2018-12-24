<?php
/**
 * Copyright © Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * See COPYING.txt for license details.
 */
namespace Faonni\SmartCategory\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Module\Setup\Migration;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Catalog\Model\Category;

/**
 * SmartCategory Install Data
 */
class InstallData implements InstallDataInterface
{
    /**
     * EAV Setup Factory
     *
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    private $_eavSetupFactory;

    /**
     * Initialize Setup
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
		EavSetupFactory $eavSetupFactory
	) {
        $this->_eavSetupFactory = $eavSetupFactory;
    }
    	
    /**
     * Installs DB Data for a Module Faonni_SmartCategory
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup->createMigrationSetup();
        $setup->startSetup();
        
        $installer->appendClassAliasReplace(
            'faonni_smartcategory_rule',
            'conditions_serialized',
            Migration::ENTITY_TYPE_MODEL,
            Migration::FIELD_CONTENT_TYPE_SERIALIZED,
            ['rule_id']
        );
        
        $installer->doUpdateClassAliases();
        
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->_eavSetupFactory->create(['setup' => $setup]);        
        $eavSetup->addAttribute(
            Category::ENTITY,
            'is_smart',
            [
				'type' => 'int',
				'label' => 'Smart Category',
				'input' => 'select',
				'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
				'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
				'required' => false,
				'sort_order' => 10,
				'default' => '0',
				'group' => 'Products in Category',
            ]
        );       
        $setup->endSetup();  
    }
}
