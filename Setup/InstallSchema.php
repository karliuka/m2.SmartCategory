<?php
/**
 * Copyright © 2011-2018 Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * 
 * See COPYING.txt for license details.
 */
namespace Faonni\SmartCategory\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Faonni_SmartCategory InstallSchema
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * Installs DB schema for a module Faonni_SmartCategory
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();

        /**
         * Create table 'faonni_smartcategory_rule'
         */
        if (!$installer->tableExists('faonni_smartcategory_rule')) {
			$table = $connection
				->newTable($installer->getTable('faonni_smartcategory_rule'))
				->addColumn(
					'rule_id',
					Table::TYPE_INTEGER,
					null,
					['unsigned' => true, 'nullable' => false, 'primary' => true],
					'Rule Id'
				)
				->addColumn(
					'conditions_serialized',
					Table::TYPE_TEXT,
					'2M',
					[],
					'Conditions Serialized'
				)
				->addColumn(
					'actions_serialized',
					Table::TYPE_TEXT,
					'2M',
					[],
					'Actions Serialized'
				)
				->setComment('Smart Category Rule');

			$connection->createTable($table);		
		} 
        $installer->endSetup();
    }
}
