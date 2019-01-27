<?php
/**
 * Copyright © 2011-2018 Karliuka Vitalii(karliuka.vitalii@gmail.com)
 *
 * See COPYING.txt for license details.
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

        $attributes = [
            [
                'value' => 'Faonni\SmartCategory\Model\Rule\Condition\Product\Sale',
                'label' => __('Special Price')
            ],
            [
                'value' => 'Faonni\SmartCategory\Model\Rule\Condition\Product\News',
                'label' => __('New')
            ]
        ];

        foreach ($productAttributes as $code => $label) {
            if ('special_price' != $code) {
                $attributes[] = [
                    'value' => 'Faonni\SmartCategory\Model\Rule\Condition\Product|' . $code,
                    'label' => $label,
                ];
            }
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
