<?php
/**
 * Copyright © 2011-2018 Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * 
 * See COPYING.txt for license details.
 */
namespace Faonni\SmartCategory\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Catalog\Model\Category;

/**
 * SmartCategory Uninstall
 */
class Uninstall implements UninstallInterface
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
     * Uninstall DB Schema for a Module SmartCategory
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

		$this->removeTables($setup);
		$this->removeColumns($setup);
        $this->removeAttributes();	
		
        $setup->endSetup();
    }
    
    /**
     * Remove Tables
	 *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function removeTables(SchemaSetupInterface $setup)
    {	
        $tableName = 'faonni_smartcategory_rule';
        if ($setup->tableExists($tableName)) {			
            $setup->getConnection()->dropTable($setup->getTable($tableName));
		}
    }
    
    /**
     * Remove Columns
	 *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function removeColumns(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->dropColumn(
			$setup->getTable('catalog_eav_attribute'), 
			'is_used_for_smart_rules'
		);
    }
    
    /**
     * Remove Attributes
     *
     * @return void
     */
    private function removeAttributes()
    {
        $attributes = ['is_smart'];
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->_eavSetupFactory->create();         
        foreach ($attributes as $attribute) {
			$eavSetup->removeAttribute(Category::ENTITY, $attribute); 	
        }
    }    
}