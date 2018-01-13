<?php
/**
 * Copyright © 2011-2018 Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * 
 * See COPYING.txt for license details.
 */
namespace Faonni\SmartCategory\Setup;

use Magento\Framework\DB\AggregatedFieldDataConverter;
use Magento\Framework\DB\DataConverter\SerializedToJson;
use Magento\Framework\DB\FieldToConvert;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * SmartCategory Upgrade Data
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Field Data Converter
     *
     * @var AggregatedFieldDataConverter
     */
    protected $_aggregatedFieldConverter;

    /**
     * Initialize Setup
     *
     * @param AggregatedFieldDataConverter $aggregatedFieldConverter
     */
    public function __construct(
        AggregatedFieldDataConverter $aggregatedFieldConverter
    ) {
        $this->_aggregatedFieldConverter = $aggregatedFieldConverter;
    }

    /**
     * Upgrades DB Data for a Module Faonni_SmartCategory
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.2.0', '<')) {
            $this->convertSerializedDataToJson($setup);
        }

        $setup->endSetup();
    }

    /**
     * Convert Metadata from Serialized to JSON Format
     *
     * @param ModuleDataSetupInterface $setup
     * @return void
     */
    private function convertSerializedDataToJson($setup)
    {
        $this->_aggregatedFieldConverter->convert([
                new FieldToConvert(
                    SerializedToJson::class,
                    $setup->getTable('faonni_smartcategory_rule'),
                    'rule_id',
                    'conditions_serialized'
                ),
            ],
            $setup->getConnection()
        );
    }
}
