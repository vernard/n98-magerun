<?php
/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Magento\Command;

use Manadev\Util\ManaRecursiveFilterIterator;
use N98\Magento\Command\AbstractMagentoCommand;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveSymlinksCommand extends AbstractMagentoCommand
{
    protected function configure(){
        $this->setName("delsymlink")
            ->setDescription("Deletes all symlinks in current working directory");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $iterator = new RecursiveDirectoryIterator(getcwd());
        $filter = new ManaRecursiveFilterIterator($iterator);
        $filterArray = $this->getApplication()->getConfig()["iterator_filter"];
        $filter->setFilters($filterArray);
        $all_files = new RecursiveIteratorIterator($filter,RecursiveIteratorIterator::SELF_FIRST);

        foreach ($all_files as $file) {
            if (file_exists($file) && is_link($file)) {
                $file = str_replace('/', '\\', $file);
                if (is_dir($file)){
                    exec("rmdir " . $file);
                }
                else{
                    exec("del " . $file);
                }
                $output->writeln("<info>Symlink deleted: {$file}</info>");
            }
        }
    }
} 