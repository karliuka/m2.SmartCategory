<?php
/**
 * Copyright © 2011-2017 Karliuka Vitalii(karliuka.vitalii@gmail.com)
 * 
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
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;

/**
 * SmartCategory AbstractIndexer model
 */
abstract class AbstractIndexer implements IndexerActionInterface, MviewActionInterface, IdentityInterface
{
    /**
     * @var IndexBuilder
     */
    protected $_indexBuilder;

    /**
     * Application Event Dispatcher
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    private $_cacheManager;

    /**
     * @var \Magento\Framework\Indexer\CacheContext
     */
    protected $_cacheContext;

    /**
     * @param IndexBuilder $indexBuilder
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        IndexBuilder $indexBuilder,
        ManagerInterface $eventManager
    ) {
        $this->_indexBuilder = $indexBuilder;
        $this->_eventManager = $eventManager;
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
        $this->_indexBuilder->reindexFull();
        $this->_eventManager->dispatch('clean_cache_by_tags', ['object' => $this]);
        //TODO: remove after fix fpc. MAGETWO-50668
        $this->getCacheManager()->clean($this->getIdentities());
    }

    /**
     * Get affected cache tags
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
     * @throws \Magento\Framework\Exception\LocalizedException
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
     * Execute partial indexation by ID list. Template method
     *
     * @param int[] $ids
     * @return void
     */
    abstract protected function doExecuteList($ids);

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @throws \Magento\Framework\Exception\LocalizedException
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
     * Execute partial indexation by ID. Template method
     *
     * @param int $id
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    abstract protected function doExecuteRow($id);

    /**
     * @return \Magento\Framework\App\CacheInterface|mixed
     *
     * @deprecated
     */
    private function getCacheManager()
    {
        if ($this->_cacheManager === null) {
            $this->_cacheManager = ObjectManager::getInstance()->get(
                'Magento\Framework\App\CacheInterface'
            );
        }
        return $this->_cacheManager;
    }

    /**
     * Get cache context
     *
     * @return \Magento\Framework\Indexer\CacheContext
     * @deprecated
     */
    protected function getCacheContext()
    {
        if (!($this->_cacheContext instanceof CacheContext)) {
            return ObjectManager::getInstance()->get(CacheContext::class);
        } else {
            return $this->_cacheContext;
        }
    }
}
