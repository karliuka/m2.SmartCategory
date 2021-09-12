<?php
/**
 * Copyright © Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * See COPYING.txt for license details.
 */
namespace Faonni\SmartCategory\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Category save
 */
class CategorySaveObserver implements ObserverInterface
{
    /**
     * Handler for category save event
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $category = $observer->getEvent()->getData('category');
        if ($category->getIsSmart()) {
            if ($category->getSmartRuleError()) {
                throw new LocalizedException(
                    $category->getSmartRuleError()
                );
            }
            $rule = $category->getSmartRule();
            if ($rule) {
                $rule->setId($category->getId());
                $rule->save();
            }
        }
    }
}
