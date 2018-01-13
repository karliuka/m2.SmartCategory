<?php
/**
 * Copyright © 2011-2018 Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * 
 * See COPYING.txt for license details.
 */
namespace Faonni\SmartCategory\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Category delete observer
 */
class CategoryDeleteObserver implements ObserverInterface
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
     * Handler for category delete event
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
		$category = $observer->getEvent()->getCategory();
		/** @var \Faonni\SmartCategory\Model\Rule $rule */
		$rule = $this->_objectManager->create('Faonni\SmartCategory\Model\Rule')
			->load($category->getId());		
		
		if ($rule->getId()) {
			$rule->delete();
		} 		
        return $this;
    }
}  
