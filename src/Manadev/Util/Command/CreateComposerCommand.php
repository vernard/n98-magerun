<?php namespace Manadev\Util\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateComposerCommand extends Command
{
    protected $dependencies = array();
    protected $composer;

    protected function configure()
    {
        $this->setName("create-composer")
            ->setDescription("Generate composer.json using the magento module declaration.")
            ->addArgument(
                "description",
                InputArgument::REQUIRED,
                "Composer description"
            )
            ->addOption(
                "OSL",
                null,
                InputOption::VALUE_NONE,
                "Sets the license to OSL 3.0 if set."
            )
            ->addOption(
                "packageName",
                null,
                InputOption::VALUE_REQUIRED,
                "Composer package name"
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->composer = new \stdClass();
        $this->dependencies["magento-hackathon/magento-composer-installer"] = "*";
        if($this->hasModuleXml())
        {
            $module_xml = simplexml_load_file($this->getModuleXmlLocation());

            // Set composer package name using module declaration.
            $this->composer->name = "manadev/" . $module_xml->modules->children()[0]->getName();

            foreach ($module_xml->modules->children() as $module) {
                if (isset($module->depends) && count($module->depends->children()) > 0) {
                    foreach ($module->depends->children() as $dependency) {
                        // Filter out Mage_* modules because they don't have a composer repository.
                        if (strpos($dependency->getName(), "Mage_") === false)
                            $this->dependencies["manadev/" . $dependency->getName()] = "*";
                    }
                }
                // Filter out the weak dependencies.
                break;
            }
        }
        else
        {
            // Get composer package name using argument.
            // Throw exception if not entered.
            if(!$input->getOption("packageName")) {
                throw new \InvalidArgumentException("module.xml not found. Please input the packageName. (e.g. 'manadev/default-theme')");
            }
            $this->composer->name = $input->getOption("packageName");
        }

        $this->composer->type = "magento-module";
        $this->composer->description = $input->getArgument("description");
        $this->composer->license = ($input->getOption("OSL")) ? "OSL 3.0" : "Proprietary license (http://www.manadev.com/terms-and-conditions)";
        $this->composer->require = $this->dependencies;
        $_minimum_stability = 'minimum-stability';
        $this->composer->$_minimum_stability = "dev";

        $contents = stripslashes(json_encode($this->composer, JSON_PRETTY_PRINT));
        file_put_contents(getcwd()."/composer.json", $contents);
    }

    protected function hasModuleXml()
    {
        return file_exists($this->getModuleXmlLocation());
    }

    protected function getModuleXmlLocation()
    {
        return realpath(getcwd() . "/module.xml");
    }
}