<?php

namespace LMO\Hook\Command;

use LMO\Hook\Checker\CheckerAbstract;
use LMO\Hook\DiffParser;
use LMO\Hook\File\Files;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class PreCommit extends Command
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var DiffParser
     */
    private $diffParser;

    /**
     * @var CheckerAbstract[]
     */
    private $checkers = [];


    /**
     * @param array             $config
     */
    public function __construct($config)
    {
        parent::__construct();
        $this->diffParser = new DiffParser();
        $this->config = $config;
    }

    protected function configure()
    {
        $this->setName('hook:pre-commit')
            ->setDescription('Git pre-commit hook');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->config['checkers'] as $checkerName => $checkerConfig) {
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
                ->setVendorBinPaths($this->config['vendorBinPaths']);
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
}
