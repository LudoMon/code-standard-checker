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
            $checkerName = lcfirst($checker->getName());
            $checker->setProjectPath($this->config['projectPath']);
            $checker->setVendorBinPath($this->config['vendorBinPath']);
            if (isset($this->config[$checkerName])) {
                $checker->setConfig($this->config[$checkerName]);
            }
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
            'git diff -U0 --diff-filter=ACMR --cached',
            $this->config['projectPath']
        );
        $process->run();

        return $this->diffParser->parse(
            $process->getOutput()
        );
    }
}
