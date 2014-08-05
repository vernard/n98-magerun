<?php namespace Manadev\Magento\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Exception\RuntimeException;

class CreateModmanCommand extends Command
{
    protected $xml;
    protected $modman;
    protected $extension;
    protected $name;

    protected function configure()
    {
        $this->setName("create-modman")
            ->setDescription("Generate modman using the extension.xml.")
            ->addOption(
                "skipFileExist",
                null,
                InputOption::VALUE_NONE,
                "Skip file exists check."
            )
            ->addOption(
                "spaceCount",
                null,
                InputOption::VALUE_REQUIRED,
                "Number of spaces for modman. Default: 48"
            );
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if(!file_exists($this->getExtensionXml()))
            throw new \RuntimeException("extension.xml not found. Unable to continue");

        $this->xml = simplexml_load_file($this->getExtensionXml());
        $this->setExtensionName($this->xml->name);
        $space_count = ($input->getOption("spaceCount")) ? $input->getOption("spaceCount") : "48" ;
        $this->createModman($input->getOption("skipFileExist"), intval($space_count));

        file_put_contents(getcwd() . '/modman', $this->modman);
    }

    protected function createModman($skip_file_exist, $space_count) {
        $reset = false;
        foreach ($this->xml->children() as $child) {
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

                $right = $this->replaceExtensionName($right);

                $spaces = $space_count - strlen($left);

                if ($spaces > 0) {
                    if (!$skip_file_exist && file_exists(getcwd() . '/' . $left))
                        $this->newLine($left, $right, $spaces);
                    elseif ($skip_file_exist)
                        $this->newLine($left, $right, $spaces);
                } else {
                    $reset = true;
                    break;
                }
            }
        }

        // Increase number of spaces by 8 if it isn't enough
        if ($reset) {
            $space_count =+ 8;
            $this->modman = '';
            $this->createModman($skip_file_exist, $space_count);
        }
    }


    protected function newLine($left, $right, $spaces) {
        $this->modman .= $left . str_repeat(" ", $spaces) . $right . "\r\n";
    }

    protected function replaceExtensionName($str) {
        $str = str_replace("{Extension/Name}", $this->extension . "/" . $this->name, $str);
        $str = str_replace("{Extension_Name}", $this->extension . "_" . $this->name, $str);
        $str = str_replace("{extension_name}", strtolower($this->extension) . "_" . strtolower($this->name), $str);
        $str = str_replace("{extension/name}", strtolower($this->extension) . "/" . strtolower($this->name), $str);

        return $str;
    }

    public function setExtensionName($xml_name) {
        $arr = explode('/', $xml_name);

        if (!isset($arr[0]) || !isset($arr[1]))
            throw new \Exception("Extension name not found in xml.");

        $this->extension = $arr[0];
        $this->name = $arr[1];
    }


    protected function getExtensionXml()
    {
        return getcwd() . "/extension.xml";
    }
}