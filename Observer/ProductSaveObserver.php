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
    protected $productRuleProcessor;

    /**
     * Intialize observer
     *
     * @param ProductRuleProcessor $productRuleProcessor
     */
    public function __construct(
        ProductRuleProcessor $productRuleProcessor
    ) {
        $this->productRuleProcessor = $productRuleProcessor;
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
            $this->productRuleProcessor->reindexRow($product->getId());
        }
    }
}
