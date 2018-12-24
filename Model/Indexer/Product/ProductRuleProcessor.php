<?php
/**
 * Copyright © Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * See COPYING.txt for license details.
 */
namespace Faonni\SmartCategory\Model\Indexer\Product;

use Magento\Framework\Indexer\AbstractProcessor;

/**
 * SmartCategory ProductRuleProcessor model
 */
class ProductRuleProcessor extends AbstractProcessor
{
    /**
     * Indexer id
     */
    const INDEXER_ID = 'smartcategory_product';

    public function reindexRow($id, $forceReindex = false)
    {
        if (!$forceReindex && $this->isIndexerScheduled()) {
            $this->getIndexer()->invalidate();
            return;
        }
        parent::reindexRow($id, $forceReindex);
    }

    public function reindexList($ids, $forceReindex = false)
    {
        if (!$forceReindex && $this->isIndexerScheduled()) {
            $this->getIndexer()->invalidate();
        }
        parent::reindexList($ids, $forceReindex);
    }


}
