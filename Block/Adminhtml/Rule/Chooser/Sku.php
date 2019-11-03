<?php
/**
 * Copyright © Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * See COPYING.txt for license details.
 */
namespace Faonni\SmartCategory\Block\Adminhtml\Rule\Chooser;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended as AbstractGrid;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as SetCollectionFactory;

/**
 * Sku grid
 *
 * @method Sku setId($id)
 * @method getId()
 * @method Sku setCheckboxCheckCallback($callback)
 * @method getCheckboxCheckCallback()
 * @method Sku setRowInitCallback($callback)
 * @method getRowInitCallback()
 * @method Sku setUseAjax($flag)
 * @method getUseAjax()
 * @method Sku setJsFormObject($object)
 * @method getJsFormObject()
 */
class Sku extends AbstractGrid
{
    /**
     * Product type
     *
     * @var ProductType
     */
    protected $productType;

    /**
     * Product collection factory
     *
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * Product collection
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $productCollection;

    /**
     * Attribute set collection factory
     *
     * @var SetCollectionFactory
     */
    protected $setCollectionFactory;

    /**
     * Intialize grid
     *
     * @param Context $context
     * @param BackendHelper $backendHelper
     * @param SetCollectionFactory $setCollectionFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ProductType $productType
     * @param array $data
     */
    public function __construct(
        Context $context,
        BackendHelper $backendHelper,
        SetCollectionFactory $setCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        ProductType $productType,
        array $data = []
    ) {
        $this->productType = $productType;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->setCollectionFactory = $setCollectionFactory;

        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
    }

    /**
     * Intialize data
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        if ($this->getRequest()->getParam('current_grid_id')) {
            $this->setId($this->getRequest()->getParam('current_grid_id'));
        } else {
            $this->setId('skuChooserGrid_' . $this->getId());
        }

        $form = $this->getJsFormObject();

        $this->setRowClickCallback("{$form}.chooserGridRowClick.bind({$form})");
        $this->setCheckboxCheckCallback("{$form}.chooserGridCheckboxCheck.bind({$form})");
        $this->setRowInitCallback("{$form}.chooserGridRowInit.bind({$form})");
        $this->setDefaultSort('sku');
        $this->setUseAjax(true);

        if ($this->getRequest()->getParam('collapse')) {
            $this->setIsCollapsed(true);
        }
    }

    /**
     * Add column filtering conditions to collection
     *
     * @param Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_products') {
            $selected = $this->getSelectedProducts();
            if (empty($selected)) {
                $selected = '';
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('sku', ['in' => $selected]);
            } else {
                $this->getCollection()->addFieldToFilter('sku', ['nin' => $selected]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Prepare Catalog Product Collection for attribute SKU in Promo Conditions SKU chooser
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->getProductCollection()
            ->setStoreId(0)
            ->addAttributeToSelect('name', 'type_id', 'attribute_set_id');

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Retrieve catalog product resource collection instance
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function getProductCollection()
    {
        if (null === $this->productCollection) {
            $this->productCollection = $this->productCollectionFactory->create();
        }
        return $this->productCollection;
    }

    /**
     * Define Cooser Grid Columns and filters
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_products',
            [
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'in_products',
                'values' => $this->getSelectedProducts(),
                'align' => 'center',
                'index' => 'sku',
                'use_index' => true
            ]
        );

        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'width' => '60px',
                'index' => 'entity_id'
            ]
        );

        $this->addColumn(
            'type',
            [
                'header' => __('Type'),
                'width' => '60px',
                'index' => 'type_id',
                'type' => 'options',
                'options' => $this->productType->getOptionArray()
            ]
        );

        $sets = $this->setCollectionFactory->create()->setEntityTypeFilter(
            $this->getProductCollection()->getEntity()->getTypeId()
        )->load()->toOptionHash();

        $this->addColumn(
            'set_name',
            [
                'header' => __('Attribute Set'),
                'width' => '100px',
                'index' => 'attribute_set_id',
                'type' => 'options',
                'options' => $sets
            ]
        );

        $this->addColumn(
            'chooser_sku',
            [
                'header' => __('SKU'),
                'name' => 'chooser_sku',
                'width' => '80px',
                'index' => 'sku'
            ]
        );

        $this->addColumn(
            'chooser_name',
            [
                'header' => __('Product'),
                'name' => 'chooser_name',
                'index' => 'name'
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * Retrieve grid reload url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            'smartcategory/*/chooser',
            ['_current' => true, 'current_grid_id' => $this->getId(), 'collapse' => null]
        );
    }

    /**
     * Retrieve selected products
     *
     * @return mixed
     */
    protected function getSelectedProducts()
    {
        return $this->getRequest()->getPost('selected', []);
    }
}
