<?php
/**
 * Copyright © Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * See COPYING.txt for license details.
 */
namespace Faonni\SmartCategory\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\DataObject;
use Faonni\SmartCategory\Model\RuleFactory;

/**
 * Category prepare observer
 */
class CategoryPrepareObserver implements ObserverInterface
{
    /**
     * Rule factory
     *
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * Intialize observer
     *
     * @param RuleFactory $ruleFactory
     */
    public function __construct(
        RuleFactory $ruleFactory
    ) {
        $this->ruleFactory = $ruleFactory;
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

        $rule = $this->ruleFactory->create();
        if ($category->getId()) {
            $rule->load($category->getId());
        }

        if ($data && $category->getIsSmart()) {
            if (isset($data['rule'])) {
                $data['conditions'] = $data['rule']['conditions'];
                unset($data['rule']);
            } else {
                // closed tab
                return;
            }
            $validateResult = $rule->validateData(new DataObject($data));
            if ($validateResult !== true) {
                $category->setSmartRuleError($validateResult);
                return;
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
    }
}
