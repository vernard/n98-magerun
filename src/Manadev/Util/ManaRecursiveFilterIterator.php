<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Util;

use RecursiveFilterIterator;

class ManaRecursiveFilterIterator extends RecursiveFilterIterator
{

    public $filters = array();

    public function accept() {
        return !in_array(
            $this->current()->getFilename(),
            $this->filters,
            true
        );
    }

    public function setFilters($filters) {
        $this->filters = $filters;
    }

} 