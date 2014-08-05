<?php
/** 
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */

namespace Manadev\Magento\Command;

use Manadev\Util\MSyncExtension;
use Manadev\Util\SymlinkMap;
use Manadev\Util\TeamConfig;
use N98\Magento\Command\AbstractMagentoCommand;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncCommand extends AbstractMagentoCommand {

    protected function configure()
    {
        $this->setName("sync")
            ->setDescription("Install extensions that are added in .team-config file");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("<info>Finding extensions...</info>");
        $extensions = $this->getExtensionList();
        $ignoredFiles = array();

        $ignoredFiles = array_merge($ignoredFiles, $this->getIgnoreList());

        $output->writeln("<info>Cleaning symlinks...</info>");
        foreach ($this->getExtensionList() as $extension) {
            $extension = new MSyncExtension($extension);
            foreach($extension->getProjectFileList() as $file)
            {
                if (file_exists($file) && is_link($file))
                {
                    $file = str_replace('/', '\\', $file);
                    if(is_dir($file))
                        exec("rmdir ".$file);
                    else
                        exec("del ". $file);
                }
            }
        }

        foreach($extensions as $extension)
        {
            $teamConfig = new TeamConfig(getcwd()."/.team-config", $extensions, getcwd()."/vendor");

            if(in_array(realpath($extension), $teamConfig->getIncludedDirectories()))
            {
                $output->writeln("<info>Installing extension:\n\t{$extension}</info>");
                $extension = new MSyncExtension($extension);

                $extensionSymlinks = $extension->getProjectFileList();
                foreach($extensionSymlinks as $key=>$value)
                {
                    $extensionSymlinks[$key] = str_replace(getcwd(),"",$value);
                }
                
                $ignoredFiles = array_merge($ignoredFiles, $extensionSymlinks);
                /** @var $map SymlinkMap */
                foreach ($extension->getSymlinkMaps() as $map) {
                    $map->createSymlink();
                }

            }
        }

        $output->writeln("<info>Creating .gitignore file...</info>");
        $this->createGitIgnoreFile($ignoredFiles);
    }

    /**
     * Returns a list of path of extensions that has an extension.xml file inside "vendor" folder
     *
     * @return array
     */
    protected function getExtensionList()
    {
        if(isset($this->extensionlist))
            return $this->extensionlist;

        $extensions = [];
        $it = new RecursiveDirectoryIterator(getcwd() . "/vendor");
        foreach (new RecursiveIteratorIterator($it) as $file) {
            if (basename($file) === "extension.xml")
                $extensions[] = dirname($file);
        }
        $this->extensionlist = $extensions;
        return $extensions;
    }

    /**
     * Returns list of files to be ignored using the file .gitignore.m-sync.template
     *
     * @return array
     */
    protected function getIgnoreList() {
        return file(getcwd()."\\.gitignore.m-sync.template", FILE_IGNORE_NEW_LINES);
    }

    /**
     * Creates a .gitignore file. Overwrite if exists.
     *
     * @param array $ignoredFiles Lines of .gitignore file.
     */
    protected function createGitIgnoreFile($ignoredFiles) {
        file_put_contents(getcwd()."/.gitignore", implode($ignoredFiles, "\r\n"));
    }
} 