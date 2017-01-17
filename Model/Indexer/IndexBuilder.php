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
 * @copyright   Copyright (c) 2017 Karliuka Vitalii(karliuka.vitalii@gmail.com) 
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Faonni\SmartCategory\Model\Indexer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Faonni\SmartCategory\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Faonni\SmartCategory\Model\Rule;
use Magento\Framework\App\ResourceConnection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexBuilder
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var RuleCollectionFactory
     */
    protected $ruleCollectionFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;
    
    /**
     * @var Product[]
     */
    protected $loadedProducts;

    /**
     * @var Category[]
     */
    protected $loadedCategories;
    
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @param RuleCollectionFactory $ruleCollectionFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        RuleCollectionFactory $ruleCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->logger = $logger;
        $this->productFactory = $productFactory;
        $this->categoryFactory = $categoryFactory;
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
            throw new \Magento\Framework\Exception\LocalizedException(
                __("Smart category rule indexing failed. See details in exception log.")
            );
        }
    }

    /**
     * Reindex by ids. Template method
     *
     * @param array $ids
     * @return void
     */
    protected function doReindexByIds($ids)
    {
        foreach ($this->getAllRules() as $rule) {
            foreach ($ids as $productId) {
                $this->applyRule($rule, $this->getProduct($productId));
            }
        }
        // run reindex category product
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
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
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
        }
        // run reindex category product
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
		$this->connection->delete(
			$this->getTable('catalog_category_product'),
			['category_id = ?' => $categoryId, 'product_id IN (?)' => $productIds]
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
        if ($rule->validate($product)) {
            // nide add product to catalog_category_product table
            return $this;
        }
        $this->cleanByIds($rule->getId(), [$product->getId()]);
        return $this;
    }

    /**
     * @param string $tableName
     * @return string
     */
    protected function getTable($tableName)
    {
        return $this->resource->getTableName($tableName);
    }
    
    /**
     * @param integer $categoryId
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
	protected function getPostedProductData($categoryId)
	{         
		$select = $this->connection
			->select()
			->from($this->getTable('catalog_category_product'), ['product_id', 'position'])
			->where('category_id = ?', $categoryId); 
				
		return $this->connection->fetchPairs($select);		
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

        $deleteIds = array_diff($postedProducts, $matchingProducts);
        $insertIds = array_diff($matchingProducts, $postedProducts);

        if (0 < count($deleteIds)) {
			$this->cleanByIds($rule->getId(), $deleteIds);
		}
		
        if (0 < count($insertIds)) {
            $data = [];
            foreach ($insertIds as $productId => $position) {
                $data[$productId] = ['category_id' => (int)$rule->getId(), 'product_id' => $productId, 'position' => $position];
            }
            $this->connection->insertMultiple($this->getTable('catalog_category_product'), $data);			
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
        $collection = $this->ruleCollectionFactory->create();
        foreach ($collection as $rule) {
			$rule->setCategory($this->getCategory($rule->getId()));
		}
		return $collection;
    }

    /**
     * @param int $productId
     * @return Product
     */
    protected function getProduct($productId)
    {
        if (!isset($this->loadedProducts[$productId])) {
            $this->loadedProducts[$productId] = $this->productFactory->create()->load($productId);
        }
        return $this->loadedProducts[$productId];
    }
    

    /**
     * @param int $categoryId
     * @return Product
     */
    protected function getCategory($categoryId)
    {
        if (!isset($this->loadedCategories[$categoryId])) {
            $this->loadedCategories[$categoryId] = $this->categoryFactory->create()->load($categoryId);
        }
        return $this->loadedCategories[$categoryId];
    }        

    /**
     * @param \Exception $e
     * @return void
     */
    protected function critical($e)
    {
        $this->logger->critical($e);
    }
}
