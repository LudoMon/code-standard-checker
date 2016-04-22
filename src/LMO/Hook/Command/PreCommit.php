<?php

namespace LMO\Hook\Command;

use LMO\Hook\Checker\CheckerAbstract;
use LMO\Hook\DiffParser;
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
    private $checkers;


    /**
     * @param array             $config
     * @param CheckerAbstract[] $checkers
     */
    public function __construct($config, $checkers)
    {
        parent::__construct();
        $this->diffParser = new DiffParser();
        $this->config = $config;
        $this->checkers = $checkers;
    }

    protected function configure()
    {
        $this->setName('hook:pre-commit')
            ->setDescription('Git pre-commit hook');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        foreach ($this->checkers as $checker) {
            $checkerName = $checker->getName();
            $checker->setProjectPath($this->config['projectPath']);
            if (isset($this->config[$checkerName])) {
                $checker->setConfig($this->config[$checkerName]);
            }
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $hasError = false;
        $output->writeln('<info>PRE-COMMIT START</info>');
        $process = new Process(
            'git diff -U0 --diff-filter=ACMR --cached',
            $this->config['projectPath']
        );
        $process->run();

        $editedFiles = $this->diffParser->parse($process->getOutput());
        foreach ($this->checkers as $checker) {
            $errorMessages = $checker->checkFiles($editedFiles);
            if (!empty($errorMessages)) {
                $hasError = true;
                $output->writeln(
                    '<error>' . $checker->getName() . ' found the following errors</error>'
                );
                foreach ($errorMessages as $errorMessage) {
                    $output->writeln($errorMessage);
                }
            }
        }

        $output->writeln('<info>PRE-COMMIT END</info>');
        return $hasError ? 1 : 0;
    }
}
