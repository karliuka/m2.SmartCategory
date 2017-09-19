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
namespace Faonni\SmartCategory\Model\Rule\Condition\Product;

use Magento\Framework\Model\AbstractModel;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Context;

/**
 * SmartCategory Rule News model
 */
class News extends AbstractCondition
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
		
        $this->setType('Faonni\SmartCategory\Model\Rule\Condition\Product\News');
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
     * @return \Faonni\SmartCategory\Model\Rule\Condition\Product\News
     */
    public function loadValueOptions()
    {
        $this->setValueOption([]);
        return $this;
    }
    
    /**
     * Prepare operator select options
     *
     * @return \Faonni\SmartCategory\Model\Rule\Condition\Product\News
     */
    public function loadOperatorOptions()
    {
        $this->setOperatorOption([
            '==' => __('set'),  
            '!=' => __('not set')
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
            'Product %1 as New',
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
            $model->getNewsFromDate(),
            $model->getNewsToDate()
        );
        
        if ($this->getOperator() == '==' && 
            ($model->getNewsFromDate() || $model->getNewsToDate()) && 
            $isDateInterval
        ) {       
            return true;
        } 
        elseif ($this->getOperator() == '!=' && 
            ((!$model->getNewsFromDate() && !$model->getNewsToDate()) || !$isDateInterval)
        ) {           
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
            ->addAttributeToSelect('news_from_date', 'left')
            ->addAttributeToSelect('news_to_date', 'left');
            
        return $this;
    }
}
