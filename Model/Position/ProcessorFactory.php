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
 * @copyright   Copyright (c) 2016 Karliuka Vitalii(karliuka.vitalii@gmail.com) 
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Faonni\SmartCategory\Model\Position;

use Magento\Framework\ObjectManagerInterface;
use Faonni\SmartCategory\Model\Position\ProcessorConfig;

/**
 * Smart Category processor factory
 */
class ProcessorFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Processor Config instance
     * 
     * @var \Faonni\SmartCategory\Model\Position\ProcessorConfig
     */
    protected $_config;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Faonni\SmartCategory\Model\Position\ProcessorConfig $config
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ProcessorConfig $config
    ) {
        $this->_objectManager = $objectManager;
        $this->_config = $config;
    }

    /**
     * Create new processor object
     *
     * @param string $processorName
     * @param array $data
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     * @return \Faonni\SmartCategory\Model\Position\ProcessorAbstract
     */
    public function create($processorName, array $data=[])
    {
        $processorClass = $this->_config->getProcessorClass($processorName);
        if (!$processorClass) {
            throw new \InvalidArgumentException("SmartCategory processor '{$processorName}' is not defined.");
        }
                
        $processorInstance = $this->_objectManager->create($processorClass, $data);
        if (!$processorInstance instanceof \Faonni\SmartCategory\Model\Position\ProcessorAbstract) {
            throw new \UnexpectedValueException(
                "Class '{$processorClass}' has to implement \\Faonni\\SmartCategory\\Model\\Position\\ProcessorAbstract."
            );
        }        
        return $processorInstance;
    }
}
