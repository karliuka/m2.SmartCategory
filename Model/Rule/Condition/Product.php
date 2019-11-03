<?php
/**
 * Copyright © Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * See COPYING.txt for license details.
 */
namespace Faonni\SmartCategory\Model\Rule\Condition;

use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\IteratorFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductCategoryList;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection as SetCollection;
use Magento\Rule\Model\Condition\Product\AbstractProduct;
use Magento\Rule\Model\Condition\Context;
use Magento\Store\Model\Store;

/**
 * Product condition
 *
 * @method getAttribute()
 * @method getJsFormObject()
 * @method getAttributeOption()
 */
class Product extends AbstractProduct
{
    /**
     * Iterator factory
     *
     * @var IteratorFactory
     */
    protected $iteratorFactory;

    /**
     * Attribute data key that indicates whether it should be used for rules
     *
     * @var string
     */
    protected $_isUsedForRuleProperty = 'is_used_for_smart_rules';

    /**
     * Initialize condition
     *
     * @param Context $context
     * @param BackendHelper $backendData
     * @param Config $config
     * @param ProductFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param ProductResource $productResource
     * @param SetCollection $attrSetCollection
     * @param FormatInterface $localeFormat
     * @param IteratorFactory $iteratorFactory
     * @param array $data
     * @param ProductCategoryList|null $categoryList
     */
    public function __construct(
        Context $context,
        BackendHelper $backendData,
        Config $config,
        ProductFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        ProductResource $productResource,
        SetCollection $attrSetCollection,
        FormatInterface $localeFormat,
        IteratorFactory $iteratorFactory,
        array $data = [],
        ProductCategoryList $categoryList = null
    ) {
        $this->iteratorFactory = $iteratorFactory;

        parent::__construct(
            $context,
            $backendData,
            $config,
            $productFactory,
            $productRepository,
            $productResource,
            $attrSetCollection,
            $localeFormat,
            $data,
            $categoryList
        );
    }

    /**
     * Retrieve value element chooser URL
     *
     * @return string
     */
    public function getValueElementChooserUrl()
    {
        $url = false;
        switch ($this->getAttribute()) {
            case 'sku':
            case 'category_ids':
                $url = 'smartcategory/rule/chooser/attribute/' . $this->getAttribute();
                if ($this->getJsFormObject()) {
                    $url .= '/form/' . $this->getJsFormObject();
                }
                break;
            default:
                break;
        }
        return $url !== false ? $this->_backendData->getUrl($url) : '';
    }

    /**
     * Validate product attribute value for condition
     *
     * @param \Magento\Catalog\Model\Product|AbstractModel $model
     * @return bool
     */
    public function validate(AbstractModel $model)
    {
        $attrCode = $this->getAttribute();
        if ('category_ids' == $attrCode) {
            return $this->validateAttribute($model->getAvailableInCategories());
        }

        $oldAttrValue = $model->getData($attrCode);
        if ($oldAttrValue === null) {
            return false;
        }

        $this->setAttributeValue($model);

        $result = $this->validateAttribute($model->getData($attrCode));
        $this->_restoreOldAttrValue($model, $oldAttrValue);

        return (bool)$result;
    }

    /**
     * Restore old attribute value
     *
     * @param AbstractModel $model
     * @param mixed $oldAttrValue
     * @return void
     */
    protected function _restoreOldAttrValue(AbstractModel $model, $oldAttrValue)
    {
        $attrCode = $this->getAttribute();
        if ($oldAttrValue === null) {
            $model->unsetData($attrCode);
        } else {
            $model->setData($attrCode, $oldAttrValue);
        }
    }

    /**
     * Set attribute value
     *
     * @param \Magento\Catalog\Model\Product|AbstractModel $model
     * @return $this
     */
    protected function setAttributeValue(AbstractModel $model)
    {
        $storeId = $model->getStoreId();
        $defaultStoreId = Store::DEFAULT_STORE_ID;

        if (!isset($this->_entityAttributeValues[$model->getId()])) {
            return $this;
        }

        $productValues  = $this->_entityAttributeValues[$model->getId()];

        if (!isset($productValues[$storeId]) && !isset($productValues[$defaultStoreId])) {
            return $this;
        }

        $value = isset($productValues[$storeId]) ? $productValues[$storeId] : $productValues[$defaultStoreId];

        $value = $this->prepareDatetimeValue($value, $model);
        $value = $this->prepareMultiselectValue($value, $model);

        $model->setData($this->getAttribute(), $value);

        return $this;
    }

    /**
     * Prepare datetime attribute value
     *
     * @param mixed $value
     * @param \Magento\Catalog\Model\Product|AbstractModel $model
     * @return mixed
     */
    protected function prepareDatetimeValue($value, AbstractModel $model)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product $resource */
        $resource = $model->getResource();
        $attribute = $resource->getAttribute($this->getAttribute());
        if ($attribute && $attribute->getBackendType() == 'datetime') {
            $value = strtotime($value);
        }
        return $value;
    }

    /**
     * Prepare multiselect attribute value
     *
     * @param mixed $value
     * @param \Magento\Catalog\Model\Product|AbstractModel $model
     * @return mixed
     */
    protected function prepareMultiselectValue($value, AbstractModel $model)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product $resource */
        $resource = $model->getResource();
        $attribute = $resource->getAttribute($this->getAttribute());
        if ($attribute && $attribute->getFrontendInput() == 'multiselect') {
            $value = strlen($value) ? explode(',', $value) : [];
        }
        return $value;
    }

    /**
     * Collect validated attributes
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function collectValidatedAttributes($productCollection)
    {
        $attribute = $this->getAttribute();
        if ('category_ids' != $attribute) {
            $productCollection->addAttributeToSelect($attribute, 'left');
            if ($this->getAttributeObject()->isScopeGlobal()) {
                $attributes = $this->getRule()->getCollectedAttributes();
                $attributes[$attribute] = true;
                $this->getRule()->setCollectedAttributes($attributes);
            } else {
                $select = clone $productCollection->getSelect();
                $attributeModel = $productCollection->getEntity()->getAttribute($attribute);

                $fieldMainTable = $productCollection->getConnection()->getAutoIncrementField(
                    $productCollection->getMainTable()
                );

                $fieldJoinTable = $attributeModel->getEntity()->getLinkField();
                $select->reset()
                    ->from(
                        ['cpe' => $productCollection->getMainTable()],
                        ['entity_id']
                    )
                    ->join(
                        ['cpa' => $attributeModel->getBackend()->getTable()],
                        'cpe.' . $fieldMainTable . ' = cpa.' . $fieldJoinTable,
                        ['store_id', 'value']
                    )->where('attribute_id = ?', (int)$attributeModel->getId());

                $iterator = $this->iteratorFactory->create();
                $res = [];
                $iterator->walk((string)$select, [function (array $data) {
                    $row = $data['row'];
                    $res[$row['entity_id']][$row['store_id']] = $row['value'];
                }], [], $productCollection->getConnection());
                $this->_entityAttributeValues = $res;
            }
        }

        return $this;
    }
}
