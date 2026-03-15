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

        /** @var \XF\Repository\RouteRepository $routeRepo */
        $routeRepo = \XF::repository('XF:Route');
        $routeRepo->rebuildRouteCaches();

        $output->writeln('<info>Done!</info>');

        return 0;
    }
}
