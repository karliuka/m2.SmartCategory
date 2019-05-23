<?php
/**
 * Copyright © Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * See COPYING.txt for license details.
 */
namespace Faonni\SmartCategory\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Faonni\SmartCategory\Model\Indexer\Product\ProductRuleProcessor;

/**
 * Product save observer
 */
class ProductSaveObserver implements ObserverInterface
{
    /**
     * Product rule processor
     *
     * @var ProductRuleProcessor
     */
    protected $_productRuleProcessor;

    /**
     * Intialize observer
     *
     * @param ProductRuleProcessor $objectManager
     */
    public function __construct(
        ProductRuleProcessor $productRuleProcessor
    ) {
        $this->_productRuleProcessor = $productRuleProcessor;
    }

    /**
     * Apply smart category rules after product model save
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        if (!$product->getIsMassupdate()) {
            $this->_productRuleProcessor->reindexRow($product->getId(),true);
        }
        return $this;
    }
}
