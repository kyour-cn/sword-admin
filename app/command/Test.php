<?php

namespace app\command;

use app\model\Task;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('test', 'test')]
class Test extends Command
{
    /**
     * @return void
     */
    protected function configure()
    {
        $this->addArgument('name', InputArgument::OPTIONAL, 'Name description');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        Task::create([
            'title' => '测试任务',
            'label' => 'Test',
            'type' => 'import',
            'params' => json_encode([]),
        ]);

        $output->writeln('Hello test');
        return self::SUCCESS;
    }

}
