<?php
/**
 * Copyright © Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * See COPYING.txt for license details.
 */
namespace Faonni\SmartCategory\Plugin\Catalog\Model\Layer\Category;

use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\Layer\Category\CollectionFilter as LayerCollectionFilter;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Category;

/**
 * CollectionFilter Plugin
 */
class CollectionFilter
{
    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_productVisibility;

    /**
     * Catalog config
     *
     * @var \Magento\Catalog\Model\Config
     */
    protected $_catalogConfig;

    /**
     * Initialize plugin
     *
     * @param Visibility $productVisibility
     * @param Config $catalogConfig
     */
    public function __construct(
        Visibility $productVisibility,
        Config $catalogConfig
    ) {
        $this->_productVisibility = $productVisibility;
        $this->_catalogConfig = $catalogConfig;
    }

    /**
     * Filter product collection
     *
     * @param LayerCollectionFilter $collectionFilter
     * @param Callable proceed
     * @param Collection $collection
     * @param Category $category
     * @return void
     */
    public function aroundFilter(
        LayerCollectionFilter $collectionFilter, 
        Callable $proceed, 
        Collection $collection, 
        Category $category
    ) {
        $collection
            ->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addUrlRewrite($category->getId());

        if ($category->getIsSmart()) {
            $collection->setVisibility(
                $this->_productVisibility->getVisibleInSiteIds()
            );
        } else {
            $collection->setVisibility(
                $this->_productVisibility->getVisibleInCatalogIds()
            );
        }
    }
} 
 
