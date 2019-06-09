<?php
/**
 * Copyright © Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * See COPYING.txt for license details.
 */
namespace Faonni\SmartCategory\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Faonni\SmartCategory\Model\RuleFactory;

/**
 * Category delete observer
 */
class CategoryDeleteObserver implements ObserverInterface
{
    /**
     * Rule factory
     *
     * @var \Faonni\SmartCategory\Model\RuleFactory
     */
    protected $_ruleFactory;

    /**
     * Intialize observer
     *
     * @param RuleFactory $ruleFactory
     */
    public function __construct(
        RuleFactory $ruleFactory
    ) {
        $this->_ruleFactory = $ruleFactory
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
        $rule = $this->_ruleFactory->create()
            ->load($category->getId());

        if ($rule->getId()) {
            $rule->delete();
        }
        return $this;
    }
}  
