<?php
/**
 * Copyright © 2011-2017 Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * 
 * See COPYING.txt for license details.
 */
namespace Faonni\SmartCategory\Model;

use Magento\Rule\Model\AbstractModel;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Serialize\Serializer\Json as Serializer;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product\Visibility;
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
     * Constants cache tag
     */    
	const CACHE_TAG = 'FAONNI_SMARTCATEGORY_RULE'; 
	
    /**
     * Model cache tag for clear cache in after save and after delete
     * When you use true - all cache will be clean
     *
     * @var string|array|bool
     */
    protected $_cacheTag = self::CACHE_TAG;   
	
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'faonni_smartcategory_rule';

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
    protected $_productsFilter;
    
    /**
     * Visibility filter flag
     *
     * @var bool
     */
    protected $_visibilityFilter = true;    

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
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;    

    /**
     * Rule constructor
	 *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param TimezoneInterface $localeDate
     * @param CollectionFactory $productCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param CombineFactory $combineFactory
     * @param ProductFactory $productFactory
     * @param Visibility $catalogProductVisibility 
     * @param Iterator $resourceIterator
     * @param ExtensionAttributesFactory|null $extensionFactory
     * @param AttributeValueFactory|null $customAttributeFactory     
     * @param Serializer $serializer    
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
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
        Visibility $catalogProductVisibility,
        Iterator $resourceIterator,
        ExtensionAttributesFactory $extensionFactory = null,
        AttributeValueFactory $customAttributeFactory = null,        
        Serializer $serializer = null,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->_combineFactory = $combineFactory;
        $this->_productFactory = $productFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_resourceIterator = $resourceIterator;

        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data,
            $extensionFactory,
            $customAttributeFactory,
            $serializer            
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
     * Get Array of Product ids Which are Matched by Rule
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
			
			$this->_eventManager->dispatch(
				'faonni_smartcategory_product_collection_match_before', 
				['rule' => $this, 'collection' => $productCollection]
			);
			
			if ($this->_productsFilter) {
				$productCollection->addIdFilter($this->_productsFilter);
			}
			
			if ($this->_visibilityFilter) {
				$productCollection->addAttributeToFilter(
					'visibility', 
					['in' => $this->_catalogProductVisibility->getVisibleInSiteIds()]
				);
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
     * Toggle visibility filter
     *
     * @param  bool $enabled
     * @return void
     */
    public function setVisibilityFilter($enabled)
    {
        $this->_visibilityFilter = $enabled;
    }

    /**
     * Check VisibilityFilter should be enabled
     *
     * @return bool
     */
    public function isVisibilityFilter()
    {
        return $this->_visibilityFilter;
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
        return $formName . 'rule_conditions_fieldset_' . $this->getRuleId();
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
		return [self::CACHE_TAG . '_' . $this->getRuleId()];
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
    
    /**
     * Validate rule data. Return true if validation passed successfully. 
     *
     * @param \Magento\Framework\DataObject $dataObject
     * @return bool|string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function validateData(DataObject $dataObject)
    {
        if ($dataObject->getIsSmart()) {
			$validator = new DataObject(['error' => null, 'continue' => true]); 			
			$this->_eventManager->dispatch(
				'faonni_smartcategory_validate_data', 
				['object' => $dataObject, 'validator' => $validator]
			);
			
            if ($validator->getError()) {
                return $validator->getError();
            }
			
			if ($validator->getContinue()) {
				$conditions = $dataObject->getConditions();
				if (!is_array($conditions) || 1 >= count($conditions)) {
					return __('Please specify a rule.');
				}
			} 
        }        
        return true;
    }      
}
