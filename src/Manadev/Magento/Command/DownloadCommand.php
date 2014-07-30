<?php namespace Manadev\Magento\Command;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class DownloadCommand extends AbstractMagentoCommand {

    protected function configure()
    {
        $this->setName("download")
            ->setDescription("Download the magento installation from Github to your machine for future use.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repo = $this->getCommandConfig()['remote-repository'];
        $dir = $this->getCommandConfig()['local-repository'];
        $dir = str_replace("%TMP%", getenv('TMP'), $dir);
        if(file_exists($dir))
            throw new \RuntimeException("Repository already exists. Please delete `${dir}` if you want to redownload.");
        else
            exec("git clone ${repo} ${dir}");
    }
}