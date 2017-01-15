<?php
/**
 * Faonni
 *  
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade module to newer
 * versions in the future.
 * 
 * @package     Faonni_SmartCategory
 * @copyright   Copyright (c) 2017 Karliuka Vitalii(karliuka.vitalii@gmail.com) 
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Faonni\SmartCategory\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Faonni_SmartCategory UpgradeSchema
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Upgrades DB schema for a module Faonni_SmartCategory
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.3', '<')) {
            $this->removeActionColumn($setup);
        }
        
        if (version_compare($context->getVersion(), '2.0.5', '<')) {
            $this->addIsUsedForSmartRulesColumn($setup);
        }
        
        $setup->endSetup();
    }

    /**
     * Remove Action Column
	 *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function removeActionColumn(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->dropColumn(
			$setup->getTable('faonni_smartcategory_rule'), 
			'actions_serialized'
		);
    }

    /**
     * add IsUsedForSmartRules Column
	 *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function addIsUsedForSmartRulesColumn(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $connection->addColumn(
            'catalog_eav_attribute',
            'is_used_for_smart_rules',
            [
                'type' => Table::TYPE_SMALLINT,
                'unsigned' => true,
                'nullable' => false,
                'default' => 0,
                'comment' => 'Is Used For Smart Category Rules',
                'after' => 'is_used_for_price_rules'
            ]
        );        
		$connection->addIndex(
			$setup->getTable('catalog_eav_attribute'),
			$setup->getIdxName('catalog_eav_attribute', ['is_used_for_smart_rules']),
			['is_used_for_smart_rules']
		);         		
    }    
}
