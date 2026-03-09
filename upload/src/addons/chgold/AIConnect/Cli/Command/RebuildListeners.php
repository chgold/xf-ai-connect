<?php

namespace chgold\AIConnect\Cli\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RebuildListeners extends Command
{
    protected function configure()
    {
        $this
            ->setName('aiconnect:rebuild-listeners')
            ->setDescription('Rebuild AI Connect event listeners');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Rebuilding AI Connect listeners...');
        
        \XF::app()->simpleCache()->delete('codeEventListeners');
        
        $output->writeln('<info>Done!</info>');
        
        return 0;
    }
}
