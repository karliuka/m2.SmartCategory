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
 * @copyright   Copyright (c) 2016 Karliuka Vitalii(karliuka.vitalii@gmail.com) 
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Faonni\SmartCategory\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface;

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
			/** @var \Faonni\SmartCategory\Model\Rule $rule */
			if ($category->getId()) {
				$rule = $this->_objectManager->create('Faonni\SmartCategory\Model\Rule')
					->load($category->getId());
			} else {
				$rule = $this->_objectManager->create('Faonni\SmartCategory\Model\Rule');	
			}						
			$validateResult = $rule->validateData(new \Magento\Framework\DataObject($data));
			// add validate control
			
			if (isset($data['rule'])) {
				$data['conditions'] = $data['rule']['conditions'];
				unset($data['rule']);
			}
			$rule->loadPost(['conditions' => $data['conditions']]);	
			$category->setPostedProducts($rule->getMatchingProductIds());
			$category->setSmartRule($rule);
		}
        return $this;
    }
}  
