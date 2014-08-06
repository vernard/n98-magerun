<?php
/** 
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
namespace Manadev\Util;

class Modman
{

    protected $maps = array();
    protected $category;
    protected $name;
    protected $extensionDir;

    public function __construct($extensionDir) {
        $this->extensionDir = $extensionDir;
        $multilines = file($extensionDir . "\\modman", FILE_IGNORE_NEW_LINES);
        foreach ($multilines as $row) {
            $parts = preg_split('/[\s\t]+/', $row, 2, PREG_SPLIT_NO_EMPTY);

            $map = new SymlinkMap($this->extensionDir . "/" . (string)$parts[0], getcwd() . "/" . (string)$parts[1]);
            $this->maps[] = $map;
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
        foreach ($this->getSymlinkMaps() as $map) {
            $list[] = $map->getTarget();
        }

        return $list;
    }

    /**
     * Returns an array of SymlinkMap of the extension
     * @return array
     */
    public function getSymlinkMaps() {
        return $this->maps;
    }
} 