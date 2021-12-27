<?php
/**
 * Copyright © Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * See COPYING.txt for license details.
 */
namespace Faonni\SmartCategory\Model\Rule\Condition\Product;

use Magento\CatalogRule\Model\ResourceModel\Rule;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Context;
use Magento\Store\Model\StoreManagerInterface;

/**
 * SmartCategory Rule Sale model
 *
 * @method Sale setType($type)
 * @method Sale setValue($value)
 * @method Sale setValueOption($option)
 * @method Sale setOperatorOption($option)
 */
class Sale extends AbstractCondition
{
    /**
     * @var array
     */
    private static array $priceRulesData = [];

    /**
     * Defines which operators will be available for this condition
     *
     * @var string
     */
    protected $_inputType = 'select';

    /**
     * Initialize Condition Model
     *
     * @param Rule $ruleResourceFactory
     * @param Session $customerSession
     * @param StoreManagerInterface $storeManager
     * @param Context $context
     * @param mixed[] $data
     */
    public function __construct(
        Rule $ruleResourceFactory,
        Session $customerSession,
        StoreManagerInterface $storeManager,
        Context $context,
        array $data = []
    ) {
        $this->ruleResourceFactory = $ruleResourceFactory;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;

        parent::__construct($context, $data);
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
     * @return $this
     */
    public function loadValueOptions()
    {
        $this->setValueOption([]);
        return $this;
    }

    /**
     * Prepare operator select options
     *
     * @return $this
     */
    public function loadOperatorOptions()
    {
        $this->setOperatorOption([
            '==' => __('has'),
            '!=' => __('does not have')
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
        return $this->getTypeElementHtml() .
            __('Product %1 a Discount', $this->getOperatorElementHtml()) .
            $this->getRemoveLinkHtml();
    }

    /**
     * Check Product for valid promo rule.
     *
     * @param $product
     * @return bool
     */
    public function checkCatalogPriceRules($product): bool
    {
        $storeId = $product->getStoreId();
        try {
            $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        } catch (NoSuchEntityException $e) {
            $websiteId = 1;
        }

        if ($product->hasCustomerGroupId()) {
            $customerGroupId = $product->getCustomerGroupId();
        } else {
            try {
                $customerGroupId = $this->customerSession->getCustomerGroupId();
            } catch (NoSuchEntityException | LocalizedException $e) {
                $customerGroupId = 0;
            }
        }

        $productId = $product->getId();
        $dateTs = $this->_localeDate->scopeTimeStamp($storeId);
        $cacheKey = date('Y-m-d', $dateTs) . "|{$websiteId}|{$customerGroupId}|{$productId}";

        if (!\array_key_exists($cacheKey, self::$priceRulesData)) {
            $rules = $this->ruleResourceFactory->getRulesFromProduct($dateTs, $websiteId, $customerGroupId, $productId);
            self::$priceRulesData[$cacheKey] = count($rules) > 0;
        }

        return self::$priceRulesData[$cacheKey] ?? false;
    }

    /**
     * Validate product attribute value for condition
     *
     * @param AbstractModel $model
     * @return bool
     */
    public function validate(AbstractModel $model)
    {
        if ($this->checkCatalogPriceRules($model)) {
            return true;
        }

        $specialPrice = $model->getSpecialPrice();
        $isDateInterval = $this->_localeDate->isScopeDateInInterval(
            $model->getStore(),
            $model->getSpecialFromDate(),
            $model->getSpecialToDate()
        );

        if ($this->getOperator() == '==' && $specialPrice && $isDateInterval) {
            return true;
        } elseif ($this->getOperator() == '!=' && (!$specialPrice || !$isDateInterval)) {
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
            ->addAttributeToSelect('special_price', 'left')
            ->addAttributeToSelect('special_from_date', 'left')
            ->addAttributeToSelect('special_to_date', 'left');

        return $this;
    }
}
