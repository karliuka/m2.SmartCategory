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
use Magento\Framework\DataObject;

/**
 * Category prepare observer
 */
class CategoryPrepareObserver implements ObserverInterface
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
     * Handler for category prepare event
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
		$request = $observer->getEvent()->getRequest();
		$category = $observer->getEvent()->getCategory();
		$data = $request->getPostValue();
		
		$rule = $this->_objectManager->create('Faonni\SmartCategory\Model\Rule');
		if ($category->getId()) {
			$rule->load($category->getId());
		}
						
		if ($data && $category->getIsSmart()) {
			if (isset($data['rule'])) {
				$data['conditions'] = $data['rule']['conditions'];
				unset($data['rule']);
			}			
							
			$validateResult = $rule->validateData(new DataObject($data));
			if ($validateResult !== true) {
				$category->setSmartRuleError($validateResult);
				return $this;
			}
			
			$rule->loadPost(['conditions' => $data['conditions']]);
			$rule->setCategory($category);
			// apply rule
			$matchingProducts = $rule->getMatchingProductIds();
			// update position
			$postedProducts = array_intersect_key($category->getPostedProducts() ?: [], $matchingProducts);
			$postedProducts = array_replace($matchingProducts, $postedProducts);

			$category->setPostedProducts($postedProducts);
			$category->setSmartRule($rule);
		} else {
			$rule->delete();
		}
	
        return $this;
    }
}  
