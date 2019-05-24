<?php
/**
 * Copyright © Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * See COPYING.txt for license details.
 */
namespace Faonni\SmartCategory\Model\Rule\Condition;

use Magento\Rule\Model\Condition\Combine as RuleCombine;
use Magento\Rule\Model\Condition\Context;
use Faonni\SmartCategory\Model\Rule\Condition\ProductFactory;
use Faonni\SmartCategory\Model\Rule\Condition\Product;
use Faonni\SmartCategory\Model\Rule\Condition\Product\News;

/**
 * Combine model
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
     * Initialize combine
     *
     * @param Context $context
     * @param ProductFactory $conditionFactory
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
        $this->setType(self::class);
    }

    /**
     * Get inherited conditions selectors
     *
     * @return array
     */
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
                'value' => News::class,
                'label' => __('New')
            ]
        ];

        foreach ($productAttributes as $code => $label) {
			$attributes[] = [
				'value' => Product::class . '|' . $code,
				'label' => $label,
			];
        }

        $conditions = parent::getNewChildSelectOptions();
        $conditions = array_merge_recursive(
            $conditions,
            [
                [
                    'value' => self::class,
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
