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

/**
 * @api
 */
interface RuleInterface extends CustomAttributesDataInterface
{
    /**
     * Constants rule id field name
     */
    const RULE_ID = 'rule_id';

    /**
     * Returns rule id field
     *
     * @return int|null
     */
    public function getRuleId();

    /**
     * @param int $ruleId
     * @return $this
     */
    public function setRuleId($ruleId);

    /**
     * Returns rule condition
     *
     * @return \Magento\SmartCategory\Api\Data\ConditionInterface|null
     */
    public function getRuleCondition();

    /**
     * @param \Magento\SmartCategory\Api\Data\ConditionInterface $condition
     * @return $this
     */
    public function setRuleCondition($condition);
}
