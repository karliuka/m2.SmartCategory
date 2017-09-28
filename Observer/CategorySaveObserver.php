<?php
/**
 * Copyright © 2011-2017 Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * 
 * See COPYING.txt for license details.
 */
namespace Faonni\SmartCategory\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Category save observer
 */
class CategorySaveObserver implements ObserverInterface
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;     
    
    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->_objectManager = $objectManager;
    }
       	
    /**
     * Handler for category save event
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
		$category = $observer->getEvent()->getCategory();		
		if ($category->getIsSmart()) {
			if ($category->getSmartRuleError()) {
				throw new LocalizedException(
					$category->getSmartRuleError()
				);
			} else {
				$rule = $category->getSmartRule();
				if ($rule) {
					$rule->setId($category->getId());
					$rule->save();					
				}				
			}				
		}
        return $this;
    }
}  
