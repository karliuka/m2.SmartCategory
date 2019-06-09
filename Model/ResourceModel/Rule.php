<?php
/**
 * Copyright © Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * See COPYING.txt for license details.
 */
namespace Faonni\SmartCategory\Model\ResourceModel;

use Magento\Rule\Model\ResourceModel\AbstractResource;

/**
 * Rule resource
 */
class Rule extends AbstractResource
{
    /**
     * Primary key auto increment flag
     *
     * @var bool
     */
    protected $_isPkAutoIncrement = false;

    /**
     * Initialize main table and table id field
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('faonni_smartcategory_rule', 'rule_id');
    }
}
