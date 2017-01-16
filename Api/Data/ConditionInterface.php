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
namespace Magento\SmartCategory\Api\Data;

use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\SmartCategory\Api\Data\ConditionExtensionInterface;

/**
 * @api
 */
interface ConditionInterface extends CustomAttributesDataInterface
{
    /**	
     * Constants defined for keys of data array
     */
    const TYPE = 'type';

    const ATTRIBUTE = 'attribute';

    const OPERATOR = 'operator';

    const VALUE = 'value';

    const IS_VALUE_PARSED = 'is_value_parsed';

    const AGGREGATOR = 'aggregator';

    const CONDITIONS = 'conditions';

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $attribute
     * @return $this
     */
    public function setAttribute($attribute);

    /**
     * @return string
     */
    public function getAttribute();

    /**
     * @param string $operator
     * @return $this
     */
    public function setOperator($operator);

    /**
     * @return string
     */
    public function getOperator();

    /**
     * @param string $value
     * @return $this
     */
    public function setValue($value);

    /**
     * @return string
     */
    public function getValue();

    /**
     * @param bool $isValueParsed
     * @return $this
     */
    public function setIsValueParsed($isValueParsed);

    /**
     * @return bool|null
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsValueParsed();

    /**
     * @param string $aggregator
     * @return $this
     */
    public function setAggregator($aggregator);

    /**
     * @return string
     */
    public function getAggregator();

    /**
     * @param \Magento\SmartCategory\Api\Data\ConditionInterface[] $conditions
     * @return $this
     */
    public function setConditions($conditions);

    /**
     * @return \Magento\SmartCategory\Api\Data\ConditionInterface[]|null
     */
    public function getConditions();

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\SmartCategory\Api\Data\ConditionExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\SmartCategory\Api\Data\ConditionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(ConditionExtensionInterface $extensionAttributes);
}
