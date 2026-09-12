<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use App\Shared\Database\Database;

Database::getConnection()->exec('DELETE FROM rate_limits');
