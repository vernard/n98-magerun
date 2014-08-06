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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

class SyncCommand extends AbstractMagentoCommand {

    protected $ignoredFiles = array();

    protected function configure()
    {
        $this->setName("sync")
            ->setDescription("Install extensions that are added in .team-config file")
            ->setDefinition(array(
                  new InputOption(
                      "debug",
                      "d",
                      InputOption::VALUE_NONE,
                      "Shows the list of extensions found, and deleted symlinks"
                  ),
                    new InputOption(
                        "skipSymlinkDelete",
                        null,
                        InputOption::VALUE_NONE,
                        "Skips the deletion of symlink"
                    ),
                ));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Get ignored file list from our gitignore template
        $ignoredFiles = $this->addToIgnoredFiles($this->getIgnoreList());

        $output->writeln("<info>Finding extensions...</info>");
        $isDebug = (boolean) $input->getOption("debug");
        $extensions = $this->getExtensionList();
        if($isDebug) {
            foreach($extensions as $extension) {
                $output->writeln("<info>Module found: {$extension}</info>");
            }
        }


        if ($input->getOption("skipSymlinkDelete")) {
            $output->writeln("<comment>Symlink deletion skipped.</comment>");
        } else {
            $output->writeln("<info>Cleaning symlinks...</info>");
            $this->doCleanSymlinks($isDebug);
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
        $this->createGitIgnoreFile();
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
    protected function createGitIgnoreFile($ignoredFiles = null) {
        if(is_null($ignoredFiles))
            $ignoredFiles = $this->ignoredFiles;
        file_put_contents(getcwd()."/.gitignore", implode($ignoredFiles, "\r\n"));
    }

    /**
     * @param $ignoredFiles
     * @return array
     */
    protected function addToIgnoredFiles($ignoredFiles) {
        $ignoredFiles = array_merge($this->ignoredFiles ,$ignoredFiles);
        $this->ignoredFiles = $ignoredFiles;

        return $ignoredFiles;
    }

    /**
     * @param boolean $isDebug
     */
    protected function doCleanSymlinks($isDebug) {
        $this->getApplication()->setAutoExit(false);
        $delsymlinkOutput = ($isDebug) ? null : new NullOutput();
        $this->getApplication()->run(new StringInput('delsymlink'), $delsymlinkOutput);
        $this->getApplication()->setAutoExit(true);
    }
} 