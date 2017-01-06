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
namespace Faonni\SmartCategory\Model;

use Magento\Rule\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ProductFactory;
use Faonni\SmartCategory\Model\Rule\Condition\CombineFactory;

/**
 * SmartCategory Rule model
 */
class Rule extends AbstractModel implements IdentityInterface
{
    /**
     * Constants rule id field name
     */
    const RULE_ID = 'rule_id';
	
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'smartcategory_rule';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'rule';

    /**
     * Store matched product Ids
     *
     * @var array
     */
    protected $_productIds;

    /**
     * Limitation for products collection
     *
     * @var int|array|null
     */
    protected $_productsFilter = null;

    /**
	 * Iterator resource model
	 *
     * @var \Magento\Framework\Model\ResourceModel\Iterator
     */
    protected $_resourceIterator;

    /**
	 * Combine model factory
	 *
     * @var \Faonni\SmartCategory\Model\Rule\Condition\CombineFactory
     */
    protected $_combineFactory;

    /**
	 * Product model factory
	 *	
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
	 * Store manager instance
	 *	
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
	 * Product collection factory
	 *	
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * Rule constructor
	 *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Faonni\SmartCategory\Model\Rule\Condition\CombineFactory $combineFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Model\ResourceModel\Iterator $resourceIterator
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate,
        CollectionFactory $productCollectionFactory,
        StoreManagerInterface $storeManager,       
        CombineFactory $combineFactory,
        ProductFactory $productFactory,
        Iterator $resourceIterator,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->_combineFactory = $combineFactory;
        $this->_productFactory = $productFactory;
        $this->_resourceIterator = $resourceIterator;

        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Init resource model and id field
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
		
        $this->_init('Faonni\SmartCategory\Model\ResourceModel\Rule');
        $this->setIdFieldName('rule_id');
    }

    /**
     * Getter for rule conditions collection
     *
     * @return \Magento\Rule\Model\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->_combineFactory->create();
    }
    
    /**
     * Getter for rule actions instance
     *
     * @return null
     */
    public function getActionsInstance()
    {
        return null;
    }
    
    /**
     * Get array of product ids which are matched by rule
     *
     * @return array
     */
    public function getMatchingProductIds()
    {
        if ($this->_productIds === null) {
            $this->_productIds = [];
            $this->setCollectedAttributes([]);
			/** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
			$productCollection = $this->_productCollectionFactory->create();
			if ($this->_productsFilter) {
				$productCollection->addIdFilter($this->_productsFilter);
			}
			$this->getConditions()->collectValidatedAttributes($productCollection);
			$this->_resourceIterator->walk(
				$productCollection->getSelect(),
				[[$this, 'callbackValidateProduct']],
				[
					'attributes' => $this->getCollectedAttributes(),
					'product' => $this->_productFactory->create()
				]
			);
        }
        return $this->_productIds;
    }

    /**
     * Callback function for product matching
     *
     * @param array $args
     * @return void
     */
    public function callbackValidateProduct($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);

        $websites = $this->_getWebsitesMap();
        $results = [];

        foreach ($websites as $websiteId => $defaultStoreId) {
            $product->setStoreId($defaultStoreId);
            $results[$websiteId] = $this->getConditions()->validate($product);
            if (true === $results[$websiteId]) {
				$this->_productIds[$product->getId()] = 1;
			}
        }
    }

    /**
     * Prepare website map
     *
     * @return array
     */
    protected function _getWebsitesMap()
    {
        $map = [];
        $websites = $this->_storeManager->getWebsites();
        foreach ($websites as $website) {
            if ($website->getDefaultStore() === null) {
                continue;
            }
            $map[$website->getId()] = $website->getDefaultStore()->getId();
        }
        return $map;
    }

    /**
     * Filtering products that must be checked for matching with rule
     *
     * @param  int|array $productIds
     * @return void
     * @codeCoverageIgnore
     */
    public function setProductsFilter($productIds)
    {
        $this->_productsFilter = $productIds;
    }

    /**
     * Returns products filter
     *
     * @return array|int|null
     * @codeCoverageIgnore
     */
    public function getProductsFilter()
    {
        return $this->_productsFilter;
    }

    /**
     * Prepare data before saving
     *
     * @return $this
     */
    public function beforeSave()
    {
        // Serialize conditions
        if ($this->getConditions()) {
            $this->setConditionsSerialized(serialize($this->getConditions()->asArray()));
            $this->_conditions = null;
        }
        return parent::beforeSave();
    }
    
    /**
     * Initialize rule model data from array
     *
     * @param array $data
     * @return $this
     */
    public function loadPost(array $data)
    {
        $arr = $this->_convertFlatToRecursive($data);      
        if (isset($arr['conditions'])) {
            $this->getConditions()->setConditions([])->loadArray($arr['conditions'][1]);
        }
        return $this;
    }

    /**
     * @param string $formName
     * @return string
     */
    public function getConditionsFieldSetId($formName='')
    {
        return $formName . 'rule_conditions_fieldset_' . $this->getId();
    }

    /**
     * Returns rule id field
     *
     * @return int|null
     */
    public function getRuleId()
    {
        return $this->getData(self::RULE_ID);
    }

    /**
     * Set rule id
     *
     * @param int $ruleId
     * @return $this
     */
    public function setRuleId($ruleId)
    {
        return $this->setData(self::RULE_ID, $ruleId);
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities()
    {
		return [];
    }
    
    /**
     * Reset rule actions
     *
     * @param null|\Magento\Rule\Model\Action\Collection $actions
     * @return $this
     */
    protected function _resetActions($actions=null)
    {
        return $this;
    }    
}
