<?php namespace Manadev\Magento\Command;

use N98\Magento\Command\AbstractMagentoCommand;
use SebastianBergmann\Exporter\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class InstallCommand extends AbstractMagentoCommand {

    protected function configure()
    {
        $this->setName("install")
            ->setDescription("Install downloaded magento installation.")
            ->setDefinition(array(
                new InputArgument('dir', InputArgument::REQUIRED),
                new InputArgument('version', InputArgument::REQUIRED)
            ));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $local = $this->getCommandConfig('Manadev\Magento\Command\DownloadCommand')['local-repository'];
        $local = str_replace("%TMP%", getenv('TMP'), $local);
        $local = realpath($local);
        if(!file_exists($local)){
            $output->writeln("<error>Local magento repository not found. Downloading...</error>", true);
            $input = new StringInput('download');
            $this->getApplication()->setAutoExit(false);
            $this->getApplication()->run($input, $output);
            $this->getApplication()->setAutoExit(true);
        }
        $dir = getcwd() . "/" . $input->getArgument('dir');
        $version = $input->getArgument('version');

        // Validate start

        // Get list of tags
        \chdir($local);
        $output = array();
        \exec("git tag", $output);

        // Check if input version has a tag
        if(!in_array($version, $output))
            throw new \RuntimeException("Magento version `{$version}` does not exist. Possible values: ". implode($output, ", "), 1);

        if(file_exists($dir))
            throw new \RuntimeException("Directory {$dir} already exists.");
        // Validate end

        \exec("git clone {$local} {$dir}");
        \chdir($dir);
        \exec("git checkout tags/${version}");
    }
}