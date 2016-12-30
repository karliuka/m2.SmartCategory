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
use Faonni\SmartCategory\Model\Position\ProcessorFactory;

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
     * Position Processor instance
     * 
     * @var \Faonni\SmartCategory\Model\Position\ProcessorFactory
     */
    protected $_processorFactory;      
    
    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Faonni\SmartCategory\Model\Position\ProcessorFactory $processorFactory
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ProcessorFactory $processorFactory
    ) {
        $this->_objectManager = $objectManager;
        $this->_processorFactory = $processorFactory;
    }
       	
    /**
     * Handler for category prepare event
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
		$category = $observer->getEvent()->getCategory();
		$request = $observer->getEvent()->getRequest();	

        return $this;
    }
}  
