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
use Faonni\SmartCategory\Model\Indexer\Product\ProductRuleProcessor;

/**
 * Product save observer
 */
class ProductSaveObserver implements ObserverInterface
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
	
    /**
     * @var ProductRuleProcessor
     */
    protected $_productRuleProcessor;	
    
    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
		ProductRuleProcessor $productRuleProcessor
    ) {
        $this->_objectManager = $objectManager;
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
			file_put_contents('/var/www/html/m2.loc/var/log/index.php', '---------------', FILE_APPEND);
			$this->_productRuleProcessor->reindexRow($product->getId());
			//$product->setOrigData('category_ids', []);
        }
        return $this;
    }
}  
