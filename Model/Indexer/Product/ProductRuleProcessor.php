<?php
/**
 * Copyright © Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * See COPYING.txt for license details.
 */
namespace Faonni\SmartCategory\Model\Indexer\Product;

use Magento\Framework\Indexer\AbstractProcessor;

/**
 * Indexer processor
 */
class ProductRuleProcessor extends AbstractProcessor
{
    /**
     * Indexer code
     */
    const INDEXER_ID = 'smartcategory_product';

    /**
     * Run Row reindex
     *
     * @param int $id
     * @param bool $forceReindex
     * @return void
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function reindexRow($id, $forceReindex = false)
    {
        if (!$forceReindex && $this->isIndexerScheduled()) {
            $this->getIndexer()->invalidate();
            return;
        }
        parent::reindexRow($id, $forceReindex);
    }

    /**
     * Run List reindex
     *
     * @param int[] $ids
     * @param bool $forceReindex
     * @return void
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function reindexList($ids, $forceReindex = false)
    {
        if (!$forceReindex && $this->isIndexerScheduled()) {
            $this->getIndexer()->invalidate();
        }
        parent::reindexList($ids, $forceReindex);
    }
}
