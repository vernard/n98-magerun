<?php
/** 
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Util;

class MSyncExtension {

    protected $maps = array();
    protected $category;
    protected $name;
    protected $extensionDir;

    public function __construct($extensionDir)
    {
        $categoryXml = simplexml_load_file($extensionDir."/extension.xml");
        $this->extensionDir = $extensionDir;
        $this->setExtensionCategoryName($categoryXml->name);

        /** @var $child \SimpleXMLElement */
        foreach($categoryXml->children() as $child)
        {
            if ($child->getName() == 'sync') {
                $left = '';
                $right = '';
                if (isset($child['extension-dir'])) {
                    $left = $child['extension-dir'];
                    $right = $child['project-dir'];
                }
                if (isset($child['extension-file'])) {
                    $left = $child['extension-file'];
                    $right = $child['project-file'];
                }

                $map = new SymlinkMap($this->extensionDir."/".(string)$left, getcwd()."/".(string)$right);
                $map->setTarget($this->replaceExtensionName($map->getTarget()));
                $this->maps[] = $map;
            }
        }
    }

    /**
     * Returns the list of files and folders that is used as project-dir/project-file
     *
     * @return array
     */
    public function getProjectFileList() {
        $list = [];
        /** @var $map SymlinkMap */
        foreach($this->getSymlinkMaps() as $map)
        {
            $list[] = $map->getTarget();
        }
        return $list;
    }

    protected function replaceExtensionName($str) {
        $str = str_replace("{Extension/Name}", $this->category . "/" . $this->name, $str);
        $str = str_replace("{Extension_Name}", $this->category . "_" . $this->name, $str);
        $str = str_replace("{extension_name}", strtolower($this->category) . "_" . strtolower($this->name), $str);
        $str = str_replace("{extension/name}", strtolower($this->category) . "/" . strtolower($this->name), $str);

        return $str;
    }

    /**
     * Set extension's category and name using "Category/Name"
     *
     * @param string $ExtensionName
     * @throws \Exception
     */
    protected function setExtensionCategoryName($ExtensionName) {
        $arr = explode('/', $ExtensionName);

        if (!isset($arr[0]) || !isset($arr[1]))
            throw new \Exception("Extension name not found in xml.");

        $this->category = $arr[0];
        $this->name = $arr[1];
    }

    /**
     * Returns an array of SymlinkMap of the extension
     * @return array
     */
    public function getSymlinkMaps() {
        return $this->maps;
    }

    public function getName()
    {
        return $this->name;
    }
} 