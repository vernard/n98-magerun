<?php
/** 
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Util;

class TeamConfig {
    /** @var \SimpleXMLElement  */
    protected $config;
   /** @var array */
    protected $directoryList;
    protected $includedDirectories;
    protected $baseDir;

    public function __construct($configFile, $directoryList, $baseDir)
    {
        $this->config = simplexml_load_file($configFile);

        $this->directoryList = $directoryList;
        foreach($this->directoryList as $key=>$value)
        {
            $this->directoryList[$key] = realpath($value);
        }

        $this->baseDir = $baseDir;
        $this->parseXml();
    }

    /**
     * Return an array of included directories
     * @return array
     */
    public function getIncludedDirectories() {
        return $this->includedDirectories;
    }

    /**
     *
     */
    protected function parseXml() {
        $_msyc = "m-sync";
        /** @var $includes \SimpleXMLElement */
        foreach($this->config->$_msyc->children()->include as $includes)
        {
            if($this->isInDirectoryList((string)$includes["dir"]))
            {
                $this->includedDirectories[] = realpath($this->baseDir."/".(string)$includes["dir"]);
            }
            elseif((string)$includes["dir"] == "")
            {
                // Include all in the directory list
                $this->includedDirectories = $this->directoryList;
            }
        }

        foreach ($this->config->$_msyc->children()->exclude as $excludes)
        {
            if ($this->isInIncludedDirectories((string)$excludes["dir"])) {
                $this->removeFromIncluded((string)$excludes["dir"]);
            }
        }
    }

    protected function isInDirectoryList($dir)
    {
        return in_array(realpath($this->baseDir . "/" . $dir), $this->directoryList);
    }

    protected function isInIncludedDirectories($dir)
    {
        return in_array(realpath($this->baseDir ."/".$dir), $this->includedDirectories);
    }

    protected function removeFromIncluded($dir) {
        if (($key = array_search(realpath($dir), $this->includedDirectories)) !== false) {
            unset($this->includedDirectories[$key]);
        }
    }


}