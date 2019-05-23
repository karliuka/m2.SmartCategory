<?php
/**
 * @author      Benjamin Rosenberger <rosenberger@e-conomix.at>
 * @package
 * @copyright   Copyright (c) 2017 E-CONOMIX GmbH (http://www.e-conomix.at)
 */

namespace Faonni\SmartCategory\Observer;


use Faonni\SmartCategory\Model\Indexer\Product\ProductRuleProcessor;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class RuleChangeObserver implements ObserverInterface
{
    /**
     * Product Rule Processor instance
     *
     * @var ProductRuleProcessor
     */
    protected $_productRuleProcessor;

    /**
     * Factory constructor
     *
     * @param ProductRuleProcessor $objectManager
     */
    public function __construct(
        ProductRuleProcessor $productRuleProcessor
    ) {
        $this->_productRuleProcessor = $productRuleProcessor;
    }

    /**
     * Apply Reindex after saving the category rule model
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $this->_productRuleProcessor->reindexAll();
    }
}
