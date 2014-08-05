<?php
/** 
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Util;

class SymlinkMap {

    protected $target;
    protected $source;
    /**
     * @param string $source
     * @param string $target
     */
    public function __construct($source, $target)
    {
        $this->source = $source;
        $this->target = $target;
    }

    public function setTarget($value) {
        $this->target = $value;
    }

    public function getTarget() {
        return $this->target;
    }

    public function createSymlink() {
        // Creating symlinks seem to work but it shows some warning.
        // Used shut up operator to hide that warning.
        @symlink($this->getSource(), $this->getTarget());
    }

    public function getSource() {
        return $this->source;
    }

    public function setSource($value) {
        $this->source = $value;
    }
} 