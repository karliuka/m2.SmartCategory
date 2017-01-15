<?php
/**
 * Faonni
 *  
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade module to newer
 * versions in the future.
 * 
 * @package     Faonni_SmartCategory
 * @copyright   Copyright (c) 2017 Karliuka Vitalii(karliuka.vitalii@gmail.com) 
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
			
		if ($data && $category->getIsSmart()) {

			$rule = $this->_objectManager->create('Faonni\SmartCategory\Model\Rule');	
			if ($category->getId()) {
				$rule->load($category->getId());
			}
			
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
		}
        return $this;
    }
}  
