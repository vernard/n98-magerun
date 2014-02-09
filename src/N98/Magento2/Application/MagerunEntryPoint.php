<?php

namespace N98\Magento2\Application;

use Magento\App\EntryPointInterface;

class MagerunEntryPoint implements EntryPointInterface
{
    /**
     * @var string
     */
    protected $_rootDir;

    /**
     * @var array
     */
    protected $_parameters;

    /**
     * @var \Magento\App\ObjectManagerFactory
     */
    protected $_locator;

    /**
     * @param string $rootDir
     * @param array $parameters
     * @param ObjectManager $objectManager
     */
    public function __construct(
        $rootDir,
        array $parameters = array(),
        ObjectManager $objectManager = null
    ) {
        $this->_rootDir = $rootDir;
        $this->_parameters = $parameters;
        $this->_locator = $objectManager;
    }

    /**
     * @param string $applicationName
     * @param array $arguments
     */
    public function run($applicationName, array $arguments = array())
    {
        $locatorFactory = new \Magento\App\ObjectManagerFactory();
        $this->_locator = $locatorFactory->create($this->_rootDir, $this->_parameters);
    }

    /**
     * @param \Magento\App\ObjectManagerFactory $locator
     */
    public function setLocator($locator)
    {
        $this->_locator = $locator;
    }

    /**
     * @return \Magento\App\ObjectManagerFactory
     */
    public function getLocator()
    {
        return $this->_locator;
    }
}