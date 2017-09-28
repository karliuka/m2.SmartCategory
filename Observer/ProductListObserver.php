<?php
/**
 * Copyright © 2011-2017 Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * 
 * See COPYING.txt for license details.
 */
namespace Faonni\SmartCategory\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Product list observer
 */
class ProductListObserver implements ObserverInterface
{	
    /**
     * Handler for product match event
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {		
		$collection = $observer->getEvent()->getCollection();
		$select = $collection->getSelect();
		$connection = $select->getConnection();	
		
		$select
			->joinLeft(
				['smart_category' => $collection->getTable('faonni_smartcategory_rule')],
				'cat_index.category_id = smart_category.rule_id',
				[]
			);		
			
        return $this;
    }
}  
