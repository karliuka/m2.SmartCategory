<?php
/**
 * Copyright © 2011-2018 Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * 
 * See COPYING.txt for license details.
 */
namespace Faonni\SmartCategory\Model\Indexer;

use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Indexer\Product\Category as ProductCategoryIndexer;
use Magento\Catalog\Model\Indexer\Category\Product as CategoryProductIndexer;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\IndexerRegistry;
use Faonni\SmartCategory\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Faonni\SmartCategory\Model\Rule;

/**
 * SmartCategory IndexBuilder model
 */
class IndexBuilder
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var RuleCollectionFactory
     */
    protected $_ruleCollectionFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;
    
    /**
     * @var Product[]
     */
    protected $_loadedProducts;
    
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;
	

    /** @var \Magento\Framework\Indexer\IndexerRegistry */
    protected $_indexerRegistry;	

    /**
     * @param RuleCollectionFactory $ruleCollectionFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        RuleCollectionFactory $ruleCollectionFactory,
        ResourceConnection $resource,
        LoggerInterface $logger,
        ProductFactory $productFactory,
		IndexerRegistry $indexerRegistry
		
    ) {
        $this->_resource = $resource;
        $this->_connection = $resource->getConnection();
        $this->_ruleCollectionFactory = $ruleCollectionFactory;
        $this->_logger = $logger;
        $this->_productFactory = $productFactory;
		$this->_indexerRegistry = $indexerRegistry;
    }

    /**
     * Reindex by id
     *
     * @param int $id
     * @return void
     * @api
     */
    public function reindexById($id)
    {
        $this->reindexByIds([$id]);
    }

    /**
     * Reindex by ids
     *
     * @param array $ids
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     * @api
     */
    public function reindexByIds(array $ids)
    {
        try {
            $this->doReindexByIds($ids);
        } catch (\Exception $e) {
            $this->critical($e);
            throw new LocalizedException(
                __("Smart category rule indexing failed. See details in exception log.")
            );
        }
    }

    /**
     * Reindex by ids
     *
     * @param array $ids
     * @return void
     */
    protected function doReindexByIds($ids)
    {
		foreach ($this->getAllRules() as $rule) {
            foreach ($ids as $productId) {
                $this->applyRule($rule, $this->getProduct($productId));
				$this->productCategoryReindexRow($productId);
            }
        }
    }
	
    /**
     * Reindex product categories by productId
     *
     * @param array $productId
     * @return void
     */
    protected function productCategoryReindexRow($productId)
    {
        $productCategoryIndexer = $this->_indexerRegistry->get(ProductCategoryIndexer::INDEXER_ID);
		$productCategoryIndexer->reindexRow($productId);
    }
	
    /**
     * Reindex category products by productId
     *
     * @param array $categoryId
     * @return void
     */
    protected function categoryProductReindexRow($categoryId)
    {
        $categoryProductIndexer = $this->_indexerRegistry->get(CategoryProductIndexer::INDEXER_ID);
		$categoryProductIndexer->reindexRow($categoryId);
    }	
	
    /**
     * Full reindex
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     * @api
     */
    public function reindexFull()
    {
        try {
            $this->doReindexFull();
        } catch (\Exception $e) {
            $this->critical($e);
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * Full reindex Template method
     *
     * @return void
     */
    protected function doReindexFull()
    {
        foreach ($this->getAllRules() as $rule) {
            $this->updateRuleProductData($rule);
			$this->categoryProductReindexRow($rule->getId());
        }
    }

    /**
     * Clean by product ids
     *
     * @param integer $categoryId
     * @param array $productIds
     * @return void
     */
    protected function cleanByIds($categoryId, $productIds)
    {
		$this->_connection->delete(
			$this->getTable('catalog_category_product'),
			['category_id = ?' => $categoryId, 'product_id IN (?)' => $productIds]
		); 	       
    }
	
    /**
     * Insert products
     *
     * @param integer $categoryId
     * @param array $productIds
     * @return void
     */
    protected function insertMultiple($categoryId, $productIds)
    {
		$data = [];
		foreach ($productIds as $productId => $position) {
			$data[] = [
				'category_id' => $categoryId, 
				'product_id' => $productId, 
				'position' => $position
			];
		}
		$this->_connection->insertMultiple(
			$this->getTable('catalog_category_product'), 
			$data
		);	       
    }
	
    /**
     * @param Rule $rule
     * @param Product $product
     * @return $this
     * @throws \Exception
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function applyRule(Rule $rule, $product)
    {
        $ruleId = $rule->getId();
		$productId = $product->getId();
		
		if ($this->validateProduct($rule, $product)) {
			if (!$this->checkPostedProduct($ruleId, $productId)) {
				$this->insertMultiple($ruleId, [$productId => '1']);
			}
            return $this;
        } else {
            $this->cleanByIds($ruleId, [$productId]);
        }

        return $this;
    }

    protected function validateProduct($rule, $product) {
        return $rule->validate($product);
    }

    /**
     * @param string $tableName
     * @return string
     */
    protected function getTable($tableName)
    {
        return $this->_resource->getTableName($tableName);
    }
	
    /**
     * @param integer $categoryId
     * @param integer $productId
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
	protected function checkPostedProduct($categoryId, $productId)
	{         
		$select = $this->_connection
			->select()
			->from($this->getTable('catalog_category_product'), [new \Zend_Db_Expr('COUNT(*)')])
			->where('category_id = ?', $categoryId)
			->where('product_id = ?', $productId); 
				
		return (0 < $this->_connection->fetchOne($select)) ? true : false;		
	}
    
    /**
     * @param integer $categoryId
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
	protected function getPostedProductData($categoryId)
	{         
		$select = $this->_connection
			->select()
			->from($this->getTable('catalog_category_product'), ['product_id', 'position'])
			->where('category_id = ?', $categoryId); 
				
		return $this->_connection->fetchPairs($select);		
	}

    /**
     * @param Rule $rule
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function updateRuleProductData(Rule $rule)
    {
        $postedProducts = $this->getPostedProductData($rule->getId()) ?: [];
        $matchingProducts = $rule->getMatchingProductIds();

        $deleteIds = array_diff_key($postedProducts, $matchingProducts);
        $insertIds = array_diff_key($matchingProducts, $postedProducts);
		
        if (0 < count($deleteIds)) {
			$this->cleanByIds($rule->getId(), array_keys($deleteIds));
		}
		
        if (0 < count($insertIds)) {
			$this->insertMultiple($rule->getId(), $insertIds);
		}		        
        return $this;
    }

    /**
     * Get active rules
     *
     * @return array
     */
    protected function getAllRules()
    {
        return $this->_ruleCollectionFactory->create();
    }

    /**
     * @param int $productId
     * @return Product
     */
    protected function getProduct($productId)
    {
        if (!isset($this->_loadedProducts[$productId])) {
            $this->_loadedProducts[$productId] = $this->_productFactory->create()
				->load($productId);
        }
        return $this->_loadedProducts[$productId];
    }      

    /**
     * @param \Exception $e
     * @return void
     */
    protected function critical($e)
    {
        $this->_logger->critical($e);
    }
}
