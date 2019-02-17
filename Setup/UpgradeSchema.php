<?php
/**
 * Copyright © Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * See COPYING.txt for license details.
 */
namespace Faonni\SmartCategory\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Upgrade schema
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Upgrades DB schema
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
     * Remove action column
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
     * add IsUsedForSmartRules column
     *
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function addIsUsedForSmartRulesColumn(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $connection->addColumn(
            $setup->getTable('catalog_eav_attribute'),
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
