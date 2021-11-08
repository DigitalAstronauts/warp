<?php

$autoloadFile = match (true) {
    file_exists(__DIR__ . '/../vendor/autoload.php') => __DIR__ . '/../vendor/autoload.php',
    file_exists(__DIR__ . '/../autoload.php') => __DIR__ . '/../autoload.php',
};

require $autoloadFile;

(new \Warp\Console\WarpApplication())->run();

