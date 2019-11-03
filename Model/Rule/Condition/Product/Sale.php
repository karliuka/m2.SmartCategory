<?php
/**
 * Copyright © Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * See COPYING.txt for license details.
 */
namespace Faonni\SmartCategory\Model\Rule\Condition\Product;

use Magento\Framework\Model\AbstractModel;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Context;

/**
 * SmartCategory Rule Sale model
 *
 * @method Sale setType($type)
 * @method Sale setValue($value)
 * @method Sale setValueOption($option)
 * @method Sale setOperatorOption($option)
 */
class Sale extends AbstractCondition
{
    /**
     * Defines which operators will be available for this condition
     *
     * @var string
     */
    protected $_inputType = 'select';

    /**
     * Initialize Condition Model
     *
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );

        $this->setType(self::class);
        $this->setValue(0);
    }

    /**
     * Get input type for attribute value
     *
     * @return string
     */
    public function getValueElementType()
    {
        return 'select';
    }

    /**
     * Prepare value select options
     *
     * @return $this
     */
    public function loadValueOptions()
    {
        $this->setValueOption([]);
        return $this;
    }

    /**
     * Prepare operator select options
     *
     * @return $this
     */
    public function loadOperatorOptions()
    {
        $this->setOperatorOption([
            '==' => __('has'),
            '!=' => __('does not have')
        ]);
        return $this;
    }

    /**
     * Get HTML of condition string
     *
     * @return string
     */
    public function asHtml()
    {
        return $this->getTypeElementHtml() . __(
            'Product %1 a Special Price',
            $this->getOperatorElementHtml()
        ) . $this->getRemoveLinkHtml();
    }

    /**
     * Validate product attribute value for condition
     *
     * @param AbstractModel $model
     * @return bool
     */
    public function validate(AbstractModel $model)
    {
        $specialPrice = $model->getSpecialPrice();
        $isDateInterval = $this->_localeDate->isScopeDateInInterval(
            $model->getStore(),
            $model->getSpecialFromDate(),
            $model->getSpecialToDate()
        );

        if ($this->getOperator() == '==' && $specialPrice && $isDateInterval) {
            return true;
        } elseif ($this->getOperator() == '!=' && (!$specialPrice || !$isDateInterval)) {
            return true;
        }
        return false;
    }

    /**
     * Collect validated attributes
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
     * @return $this
     */
    public function collectValidatedAttributes($productCollection)
    {
        $productCollection
            ->addAttributeToSelect('special_price', 'left')
            ->addAttributeToSelect('special_from_date', 'left')
            ->addAttributeToSelect('special_to_date', 'left');

        return $this;
    }
}
