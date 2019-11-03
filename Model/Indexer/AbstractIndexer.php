<?php
/**
 * Copyright © Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * See COPYING.txt for license details.
 */
namespace Faonni\SmartCategory\Model\Indexer;

use Magento\Framework\Mview\ActionInterface as MviewActionInterface;
use Magento\Framework\Indexer\ActionInterface as IndexerActionInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Indexer\CacheContext;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\App\Cache\Type\Block;
use Magento\Framework\App\CacheInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;

/**
 * Abstract indexer
 */
abstract class AbstractIndexer implements IndexerActionInterface, MviewActionInterface, IdentityInterface
{
    /**
     * Index builder
     *
     * @var IndexBuilder
     */
    protected $indexBuilder;

    /**
     * Application event dispatcher
     *
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * Cache manager
     *
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cacheManager;

    /**
     * Cache context
     *
     * @var \Magento\Framework\Indexer\CacheContext
     */
    protected $cacheContext;

    /**
     * Initialize indexer
     *
     * @param IndexBuilder $indexBuilder
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        IndexBuilder $indexBuilder,
        ManagerInterface $eventManager
    ) {
        $this->indexBuilder = $indexBuilder;
        $this->eventManager = $eventManager;
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     */
    public function execute($ids)
    {
        $this->executeList($ids);
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
        $this->indexBuilder->reindexFull();
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this]);

        $this->getCacheManager()->clean($this->getIdentities());
    }

    /**
     * Retrieve affected cache tags
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getIdentities()
    {
        return [
            Category::CACHE_TAG,
            Product::CACHE_TAG,
            Block::CACHE_TAG
        ];
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @throws LocalizedException
     * @return void
     */
    public function executeList(array $ids)
    {
        if (!$ids) {
            throw new LocalizedException(
                __('Could not rebuild index for empty products array')
            );
        }
        $this->doExecuteList($ids);
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     */
    abstract protected function doExecuteList($ids);

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @throws LocalizedException
     * @return void
     */
    public function executeRow($id)
    {
        if (!$id) {
            throw new LocalizedException(
                __('We can\'t rebuild the index for an undefined product.')
            );
        }
        $this->doExecuteRow($id);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @throws LocalizedException
     * @return void
     */
    abstract protected function doExecuteRow($id);

    /**
     * Retrieve cache manager
     *
     * @return \Magento\Framework\App\CacheInterface|mixed
     * @deprecated
     */
    protected function getCacheManager()
    {
        if ($this->cacheManager === null) {
            $this->cacheManager = ObjectManager::getInstance()->get(
                CacheInterface::class
            );
        }
        return $this->cacheManager;
    }

    /**
     * Retrieve cache context
     *
     * @return \Magento\Framework\Indexer\CacheContext
     * @deprecated
     */
    protected function getCacheContext()
    {
        if (!($this->cacheContext instanceof CacheContext)) {
            return ObjectManager::getInstance()->get(CacheContext::class);
        } else {
            return $this->cacheContext;
        }
    }
}
