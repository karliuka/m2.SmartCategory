<?php
/**
 * Copyright © Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * See COPYING.txt for license details.
 */
namespace Faonni\SmartCategory\Model\Indexer;

use Magento\Framework\DataObject;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Indexer\Product\Category as ProductCategoryIndexer;
use Magento\Catalog\Model\Indexer\Category\Product as CategoryProductIndexer;
use Psr\Log\LoggerInterface;
use Faonni\SmartCategory\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;
use Faonni\SmartCategory\Model\Rule;

/**
 * Index builder
 */
class IndexBuilder
{
    /**
     * Resource connection
     *
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * Rule collection factory
     *
     * @var RuleCollectionFactory
     */
    protected $ruleCollectionFactory;

    /**
     * Logger interface
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Product factory
     *
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * Loaded products
     *
     * @var array
     */
    protected $loadedProducts;

    /**
     * Adapter interface
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * Indexer registry
     *
     * @var IndexerRegistry
     */
    protected $indexerRegistry;

    /**
     * Initialize builder
     *
     * @param RuleCollectionFactory $ruleCollectionFactory
     * @param ResourceConnection $resource
     * @param LoggerInterface $logger
     * @param ProductFactory $productFactory
     * @param IndexerRegistry $indexerRegistry
     */
    public function __construct(
        RuleCollectionFactory $ruleCollectionFactory,
        ResourceConnection $resource,
        LoggerInterface $logger,
        ProductFactory $productFactory,
        IndexerRegistry $indexerRegistry
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->logger = $logger;
        $this->productFactory = $productFactory;
        $this->indexerRegistry = $indexerRegistry;
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
     * @throws LocalizedException
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
     * @param int $productId
     * @return void
     */
    protected function productCategoryReindexRow($productId)
    {
        $productCategoryIndexer = $this->indexerRegistry->get(ProductCategoryIndexer::INDEXER_ID);
        $productCategoryIndexer->reindexRow($productId);
    }

    /**
     * Reindex category products by productId
     *
     * @param int $categoryId
     * @return void
     */
    protected function categoryProductReindexRow($categoryId)
    {
        $categoryProductIndexer = $this->indexerRegistry->get(CategoryProductIndexer::INDEXER_ID);
        $categoryProductIndexer->reindexRow($categoryId);
    }

    /**
     * Full reindex
     *
     * @throws LocalizedException
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
        $this->connection->delete(
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
        $this->connection->insertMultiple(
            $this->getTable('catalog_category_product'),
            $data
        );
    }

    /**
     * Apply rule
     *
     * @param Rule $rule
     * @param DataObject $product
     * @return $this
     */
    protected function applyRule(Rule $rule, $product)
    {
        $ruleId = $rule->getId();
        $productId = $product->getId();

        if ($rule->validate($product)) {
            if (!$this->checkPostedProduct($ruleId, $productId)) {
                $this->insertMultiple($ruleId, [$productId => '1']);
            }
            return $this;
        }
        $this->cleanByIds($ruleId, [$productId]);
        return $this;
    }

    /**
     * Retrieve table name
     *
     * @param string $tableName
     * @return string
     */
    protected function getTable($tableName)
    {
        return $this->resource->getTableName($tableName);
    }

    /**
     * Check posted product
     *
     * @param string $categoryId
     * @param string $productId
     * @return bool
     */
    protected function checkPostedProduct($categoryId, $productId)
    {
        $select = $this->connection
            ->select()
            ->from($this->getTable('catalog_category_product'), [new \Zend_Db_Expr('COUNT(*)')])
            ->where('category_id = ?', $categoryId)
            ->where('product_id = ?', $productId);

        return (0 < $this->connection->fetchOne($select)) ? true : false;
    }

    /**
     * Retrieve posted products
     *
     * @param string $categoryId
     * @return array
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
     * Update posted products
     *
     * @param Rule $rule
     * @return $this
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
     * Retrieve active rules
     *
     * @return \Faonni\SmartCategory\Model\ResourceModel\Rule\Collection
     */
    protected function getAllRules()
    {
        return $this->ruleCollectionFactory->create();
    }

    /**
     * Retrieve product
     *
     * @param int $productId
     * @return DataObject
     */
    protected function getProduct($productId)
    {
        if (!isset($this->loadedProducts[$productId])) {
            $this->loadedProducts[$productId] = $this->productFactory->create()
                ->load($productId);
        }
        return $this->loadedProducts[$productId];
    }

    /**
     * Add critical message
     *
     * @param \Exception $e
     * @return void
     */
    protected function critical($e)
    {
        $this->logger->critical($e);
    }
}
