<?php

namespace LMO\CodeStandard\Command;

use LMO\CodeStandard\Checker\CheckerAbstract;
use LMO\CodeStandard\DiffParser;
use LMO\CodeStandard\File\Files;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class CheckStaged extends Command
{
    private $scriptPath;

    /**
     * @var array
     */
    private $checkersConfig;

    /**
     * @var array
     */
    private $vendorDirectories;

    /**
     * @var DiffParser
     */
    private $diffParser;

    /**
     * @var CheckerAbstract[]
     */
    private $checkers = [];


    /**
     * @param string $scriptPath
     * @param array  $checkersConfig
     */
    public function __construct($scriptPath, $checkersConfig)
    {
        parent::__construct();
        $this->diffParser = new DiffParser();
        $this->checkersConfig = $checkersConfig;
        $this->scriptPath = $scriptPath;
        $this->initVendorDirectories($scriptPath);
    }

    protected function configure()
    {
        $this->setName('code-standard:check:staged')
            ->setDescription('Check Git staged changes violations');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->checkersConfig as $checkerName => $checkerConfig) {
            if (isset($checkerConfig['enable']) && !$checkerConfig['enable']) {
                break;
            }
            if (!class_exists($checkerConfig['class'])) {
                throw new \InvalidArgumentException(
                    'Class not found: ' . $checkerConfig['class']
                );
            }
            $checker = new $checkerConfig['class'];
            if (!($checker instanceof CheckerAbstract)) {
                throw new \InvalidArgumentException(
                    'A checker must extend CheckerAbstract'
                );
            }
            $checker->setName(ucfirst($checkerName))
                ->setVendorDirectories($this->vendorDirectories)
                ->setScriptPath($this->scriptPath);
            if (!empty($checkerConfig['options'])) {
                $checker->setConfig($checkerConfig['options']);
            }
            $this->checkers[] = $checker;
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $hasError = false;
        $this->printStartMessage($output);
        $editedFiles = $this->getEditedFiles();
        foreach ($this->checkers as $checker) {
            $errorMessages = $checker->checkFiles($editedFiles);
            if (!empty($errorMessages)) {
                $hasError = true;
                $output->writeln('');
                $output->writeln(
                    '<bg=yellow;fg=black>' .
                    $checker->getName() . ' found the following errors</>'
                );
                foreach ($errorMessages as $errorMessage) {
                    $output->writeln($errorMessage);
                }
            }
        }

        $this->printEndMessage($output, $hasError);
        return $hasError ? 1 : 0;
    }

    /**
     * @param OutputInterface $output
     * @return void
     */
    private function printStartMessage(OutputInterface $output)
    {
        $output->writeln('<question>                             </question>');
        $output->writeln('<question>       PRE-COMMIT HOOK       </question>');
        $output->writeln('<question>                             </question>');
    }

    /**
     * @param OutputInterface $output
     * @param bool            $hasError
     * @return void
     */
    private function printEndMessage(OutputInterface $output, $hasError)
    {
        $output->writeln('');
        if (!$hasError) {
            $output->writeln('<fg=black;bg=green>                             </>');
            $output->writeln('<fg=black;bg=green>       COMMIT ACCEPTED       </>');
            $output->writeln('<fg=black;bg=green>                             </>');
        } else {
            $output->writeln('<error>                             </error>');
            $output->writeln('<error>       COMMIT REJECTED       </error>');
            $output->writeln('<error>   (git commit --no-verify)  </error>');
            $output->writeln('<error>                             </error>');
        }
    }

    /**
     * @return Files
     */
    private function getEditedFiles()
    {
        $process = new Process(
            'git diff -U0 --diff-filter=ACMR --cached'
        );
        $process->run();

        return $this->diffParser->parse(
            $process->getOutput()
        );
    }

    /**
     * @param string $scriptPath
     * @return void
     */
    private function initVendorDirectories($scriptPath)
    {
        $this->vendorDirectories = [
            'composer' => $scriptPath . DIRECTORY_SEPARATOR .
                implode(DIRECTORY_SEPARATOR, ['vendor', 'bin']) . DIRECTORY_SEPARATOR,
            'node' => $scriptPath . DIRECTORY_SEPARATOR .
                implode(DIRECTORY_SEPARATOR, ['node_modules', '.bin']) . DIRECTORY_SEPARATOR
        ];
    }
}
