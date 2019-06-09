<?php
/**
 * Copyright © Karliuka Vitalii(karliuka.vitalii@gmail.com)
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
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product\Visibility;
use Faonni\SmartCategory\Model\Rule\Condition\CombineFactory;
use Faonni\SmartCategory\Model\ResourceModel\Rule as RuleResource;

/**
 * Rule model
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
    protected $productIds;

    /**
     * Limitation for products collection
     *
     * @var int|array|null
     */
    protected $productsFilter;

    /**
     * Visibility filter flag
     *
     * @var bool
     */
    protected $visibilityFilter = true;

    /**
     * Iterator resource model
     *
     * @var \Magento\Framework\Model\ResourceModel\Iterator
     */
    protected $resourceIterator;

    /**
     * Combine model factory
     *
     * @var \Faonni\SmartCategory\Model\Rule\Condition\CombineFactory
     */
    protected $combineFactory;

    /**
     * Product model factory
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $catalogProductVisibility;

    /**
     * Initialize model
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
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
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
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->storeManager = $storeManager;
        $this->combineFactory = $combineFactory;
        $this->productFactory = $productFactory;
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->resourceIterator = $resourceIterator;

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
        $this->_init(RuleResource::class);
    }

    /**
     * Retrieve rule conditions collection
     *
     * @return \Magento\Rule\Model\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->combineFactory->create();
    }

    /**
     * Retrieve rule actions instance
     *
     * @return null
     */
    public function getActionsInstance()
    {
        return null;
    }

    /**
     * Retrieve array of product ids which are matched by rule
     *
     * @return array
     */
    public function getMatchingProductIds()
    {
        if ($this->productIds === null) {
            $this->productIds = [];
            $this->setCollectedAttributes([]);
            /** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
            $productCollection = $this->productCollectionFactory->create();

            $this->_eventManager->dispatch(
                'faonni_smartcategory_product_collection_match_before',
                ['rule' => $this, 'collection' => $productCollection]
            );

            if ($this->productsFilter) {
                $productCollection->addIdFilter($this->productsFilter);
            }

            if ($this->visibilityFilter) {
                $productCollection->addAttributeToFilter(
                    'visibility',
                    ['in' => $this->catalogProductVisibility->getVisibleInSiteIds()]
                );
            }

            $this->getConditions()->collectValidatedAttributes($productCollection);
            $this->resourceIterator->walk(
                $productCollection->getSelect(),
                [[$this, 'callbackValidateProduct']],
                [
                    'attributes' => $this->getCollectedAttributes(),
                    'product' => $this->productFactory->create()
                ]
            );
        }
        return $this->productIds;
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

        $websites = $this->getWebsitesMap();
        $results = [];

        foreach ($websites as $websiteId => $defaultStoreId) {
            $product->setStoreId($defaultStoreId);
            $results[$websiteId] = $this->getConditions()->validate($product);
            if (true === $results[$websiteId]) {
                $this->productIds[$product->getId()] = 1;
            }
        }
    }

    /**
     * Prepare website map
     *
     * @return array
     */
    protected function getWebsitesMap()
    {
        $map = [];
        $websites = $this->storeManager->getWebsites();
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
     */
    public function setProductsFilter($productIds)
    {
        $this->productsFilter = $productIds;
    }

    /**
     * Retrieve products filter
     *
     * @return array|int|null
     */
    public function getProductsFilter()
    {
        return $this->productsFilter;
    }

    /**
     * Toggle visibility filter
     *
     * @param  bool $enabled
     * @return void
     */
    public function setVisibilityFilter($enabled)
    {
        $this->visibilityFilter = $enabled;
    }

    /**
     * Check VisibilityFilter should be enabled
     *
     * @return bool
     */
    public function isVisibilityFilter()
    {
        return $this->visibilityFilter;
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
     * Retrieve condition field set id
     *
     * @param string $formName
     * @return string
     */
    public function getConditionsFieldSetId($formName = '')
    {
        return $formName . 'rule_conditions_fieldset_' . $this->getRuleId();
    }

    /**
     * Retrieve rule id field
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
     * Retrieve unique ID(s) for each object in system
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
    protected function _resetActions($actions = null)
    {
        return $this;
    }

    /**
     * Validate rule data
     *
     * @param \Magento\Framework\DataObject $dataObject
     * @return bool|string
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
