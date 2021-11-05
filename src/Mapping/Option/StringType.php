<?php
declare(strict_types=1);

namespace Warp\Mapping\Option;

use Phinx\Db\Adapter\MysqlAdapter;

class StringType
{
    const LONG_TEXT = MysqlAdapter::TEXT_LONG;
}