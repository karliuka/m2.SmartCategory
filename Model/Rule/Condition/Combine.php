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
namespace Faonni\SmartCategory\Model\Rule\Condition;

use Magento\Rule\Model\Condition\Combine as RuleCombine;
use Magento\Rule\Model\Condition\Context;
use Faonni\SmartCategory\Model\Rule\Condition\ProductFactory;

/**
 * SmartCategory Rule Combine model
 */
class Combine extends RuleCombine
{
    /**
	 * Product model factory
	 *	
     * @var \Faonni\SmartCategory\Model\Rule\Condition\ProductFactory
     */	 
    protected $_productFactory;

    /**
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Faonni\SmartCategory\Model\Rule\Condition\ProductFactory $conditionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProductFactory $conditionFactory,
        array $data = []
    ) {
        $this->_productFactory = $conditionFactory;	
		
        parent::__construct(
			$context, 
			$data
		);
        $this->setType('Faonni\SmartCategory\Model\Rule\Condition\Combine');
    }

    /**
     * Get inherited conditions selectors
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $productAttributes = $this->_productFactory->create()
			->loadAttributeOptions()
			->getAttributeOption();
			
        $attributes = [];
        foreach ($productAttributes as $code => $label) {
            $attributes[] = [
                'value' => 'Faonni\SmartCategory\Model\Rule\Condition\Product|' . $code,
                'label' => $label,
            ];
        }
        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'value' => 'Faonni\SmartCategory\Model\Rule\Condition\Combine',
                    'label' => __('Conditions Combination'),
                ],
                ['label' => __('Product Attribute'), 'value' => $attributes]
            ]
        );
        return $conditions;
    }

    /**
     * Collect validated attributes
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
     * @return $this
     */
    public function collectValidatedAttributes($productCollection)
    {
        foreach ($this->getConditions() as $condition) {
            $condition->collectValidatedAttributes($productCollection);
        }
        return $this;
    }
}
