<?php

namespace chgold\AIConnect\Cli\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RebuildRoutes extends Command
{
    protected function configure()
    {
        $this
            ->setName('aiconnect:rebuild-routes')
            ->setDescription('Rebuild AI Connect routes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Rebuilding AI Connect routes...');

        \XF::app()->simpleCache()->delete('routesPublic');
        \XF::app()->simpleCache()->delete('routesApi');

        $output->writeln('<info>Done!</info>');

        return 0;
    }
}
