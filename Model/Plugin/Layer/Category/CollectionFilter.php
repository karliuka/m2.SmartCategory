<?php
/**
 * Copyright © 2011-2017 Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * 
 * See COPYING.txt for license details.
 */
namespace Faonni\SmartCategory\Model\Plugin\Layer\Category;

use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\Layer\Category\CollectionFilter as LayerCollectionFilter;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Category;

/**
 * Collection filter
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
     * CollectionFilter constructor
     *
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\Catalog\Model\Config $catalogConfig
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
     * @param \Magento\Catalog\Model\Layer\Category\CollectionFilter" $collectionFilter
     * @param callable proceed
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param \Magento\Catalog\Model\Category $category
     * @return void
     */
    public function aroundFilter(LayerCollectionFilter $collectionFilter, callable $proceed, Collection $collection, Category $category)
    {
        $collection
            ->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addUrlRewrite($category->getId());
            	
		if ($category->getIsSmart()) {
			$collection->setVisibility($this->_productVisibility->getVisibleInSiteIds());
		} else {
			$collection->setVisibility($this->_productVisibility->getVisibleInCatalogIds());
		}
    }
} 
 
