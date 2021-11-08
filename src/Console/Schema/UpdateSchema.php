<?php

declare(strict_types=1);

namespace Warp\Console\Schema;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateSchema extends Command
{
    protected static $defaultName = 'warp:schema-tool:update';
    protected static $defaultDescription = 'Make migration according defined schema.';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return self::SUCCESS;
    }
}