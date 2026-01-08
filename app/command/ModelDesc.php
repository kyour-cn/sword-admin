<?php

namespace app\command;

use app\common\utils\ModelDescGen;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('model:desc', '生成模型注释')]
class ModelDesc extends Command
{
    /**
     * @return void
     */
    protected function configure()
    {
        $this->setDescription('生成模型注释')
             ->addArgument('model', InputArgument::OPTIONAL, '模型文件路径，如：app/model/App.php')
             ->addOption('all', null, InputOption::VALUE_NONE, '生成全部模型注释');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $modelDescGen = new ModelDescGen();
        
        // 获取参数和选项
        $modelFile = $input->getArgument('model');
        $allModels = $input->getOption('all');
        
        if ($modelFile) {
            // 生成单个模型文件注释
            $output->writeln("正在生成模型文件注释: {$modelFile}");
            
            // 处理相对路径
            if (!file_exists($modelFile)) {
                $absolutePath = dirname(__DIR__, 3) . '/' . ltrim($modelFile, '/');
                if (file_exists($absolutePath)) {
                    $modelFile = $absolutePath;
                }
            }
            
            if ($modelDescGen->genModeDesc($modelFile)) {
                $output->writeln("<info>模型文件注释生成成功: {$modelFile}</info>");
            } else {
                $output->writeln("<error>模型文件注释生成失败: {$modelFile}</error>");
                return self::FAILURE;
            }
        } else {
            // 生成全部模型注释
            $output->writeln("正在生成全部模型注释...");
            $modelDescGen->genAllModelDesc();
            $output->writeln("<info>全部模型注释生成完成！</info>");
        }
        
        return self::SUCCESS;
    }

}