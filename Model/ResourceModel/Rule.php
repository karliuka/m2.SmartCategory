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
namespace Faonni\SmartCategory\Model\ResourceModel;

use Magento\Rule\Model\ResourceModel\AbstractResource;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\ObjectManagerInterface;

/**
 * SmartCategory Rule resource
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
     * Rule constructor
     * 
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        $connectionName = null
    ) {            
        parent::__construct(
			$context, 
			$connectionName
		);
    }

    /**
     * Initialize main table and table id field
     *
     * @return void
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        $this->_init('faonni_smartcategory_rule', 'rule_id');
    }
}
