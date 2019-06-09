<?php
/**
 * Copyright © Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * See COPYING.txt for license details.
 */
namespace Faonni\SmartCategory\Model\ResourceModel\Rule;

use Magento\Rule\Model\ResourceModel\Rule\Collection\AbstractCollection;

/**
 * SmartCategory Rule collection
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
        $this->_init(
			'Faonni\SmartCategory\Model\Rule', 
			'Faonni\SmartCategory\Model\ResourceModel\Rule'
		);
    }
}
