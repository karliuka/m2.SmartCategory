<?php
/**
 * Copyright © 2011-2018 Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * 
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
     * Apply smart category rules after product model save
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
		$product = $observer->getEvent()->getProduct();		
        if (!$product->getIsMassupdate()) {           
			$this->_productRuleProcessor->reindexRow($product->getId());
        }
        return $this;
    }
}  
