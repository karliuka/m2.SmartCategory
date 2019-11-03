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
     * @var Visibility
     */
    protected $productVisibility;

    /**
     * Catalog config
     *
     * @var Config
     */
    protected $catalogConfig;

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
        $this->productVisibility = $productVisibility;
        $this->catalogConfig = $catalogConfig;
    }

    /**
     * Filter product collection
     *
     * @param LayerCollectionFilter $collectionFilter
     * @param callable $proceed
     * @param Collection $collection
     * @param Category $category
     * @return void
     */
    public function aroundFilter(
        LayerCollectionFilter $collectionFilter,
        callable $proceed,
        Collection $collection,
        Category $category
    ) {
        $collection
            ->addAttributeToSelect($this->catalogConfig->getProductAttributes())
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addUrlRewrite($category->getId());

        if ($category->getIsSmart()) {
            $collection->setVisibility(
                $this->productVisibility->getVisibleInSiteIds()
            );
        } else {
            $collection->setVisibility(
                $this->productVisibility->getVisibleInCatalogIds()
            );
        }
    }
}
