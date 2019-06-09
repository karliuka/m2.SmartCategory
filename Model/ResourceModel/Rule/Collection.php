<?php
/**
 * Copyright © Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * See COPYING.txt for license details.
 */
namespace Faonni\SmartCategory\Model\ResourceModel\Rule;

use Magento\Rule\Model\ResourceModel\Rule\Collection\AbstractCollection;
use Faonni\SmartCategory\Model\ResourceModel\Rule as RuleResource;
use Faonni\SmartCategory\Model\Rule;

/**
 * Rule collection
 */
class Collection extends AbstractCollection
{
    /**
     * Set resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Rule::class, RuleResource::class);
    }
}
