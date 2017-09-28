<?php
/**
 * Copyright © 2011-2017 Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * 
 * See COPYING.txt for license details.
 */
namespace Faonni\SmartCategory\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Module\Setup\Migration;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Catalog\Model\Category;

/**
 * Faonni_SmartCategory InstallData
 */
class InstallData implements InstallDataInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $_eavSetupFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
		EavSetupFactory $eavSetupFactory
	) {
        $this->_eavSetupFactory = $eavSetupFactory;
    }
    	
    /**
     * Installs DB schema for a module Faonni_SmartCategory
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
        
        /** @var EavSetup $eavSetup */
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
