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

/**
 * smart category processor config 
 */
class ProcessorConfig
{
    /**
     * Processor config list
     * 
     * @var array
     */
    private $_config;

    /**
     * Validate format of processors configuration array
     *
     * @param array $config
     * @throws \InvalidArgumentException
     */
    public function __construct(array $config)
    {
        foreach ($config as $processorName => $processorInfo) {
            if (!is_string($processorName) || empty($processorName)) {
                throw new \InvalidArgumentException('Name for a smart category processor has to be specified.');
            }
            if (empty($processorInfo['class'])) {
                throw new \InvalidArgumentException('Class for a smart category processor has to be specified.');
            }
            if (empty($processorInfo['label'])) {
                throw new \InvalidArgumentException('Label for a smart category processor has to be specified.');
            }
        }
        $this->_config = $config;
    }

    /**
     * Retrieve unique names of all available processors
     *
     * @return array
     */
    public function getAvailableProcessors()
    {
        return array_keys($this->_config);
    }

    /**
     * Retrieve name of a class that corresponds to processor name
     *
     * @param string $processorName
     * @return string|null
     */
    public function getProcessorClass($processorName)
    {
        if (isset($this->_config[$processorName]['class'])) {
            return $this->_config[$processorName]['class'];
        }
        return null;
    }

    /**
     * Retrieve already translated label that corresponds to processor name
     *
     * @param string $processorName
     * @return \Magento\Framework\Phrase|null
     */
    public function getProcessorLabel($processorName)
    {
        if (isset($this->_config[$processorName]['label'])) {
            return __($this->_config[$processorName]['label']);
        }
        return null;
    }  
}
