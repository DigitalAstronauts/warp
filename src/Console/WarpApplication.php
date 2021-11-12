<?php

declare(strict_types=1);

namespace Warp\Console;

use Phinx\Console\PhinxApplication;
use Warp\Console\Schema\UpdateSchema;

class WarpApplication extends PhinxApplication
{

    public function __construct()
    {
        parent::__construct();
        $this->setName('Warp ORM - Console application base on: Phinx by CakePHP');
        $this->addCommands([
            new UpdateSchema()
        ]);
    }
}