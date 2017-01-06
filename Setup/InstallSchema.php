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
