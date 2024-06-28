<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    // Define the paths to refactor
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/test',
    ]);

    // Set the target PHP version to 8.1
    $rectorConfig->sets([
        \Rector\Set\ValueObject\LevelSetList::UP_TO_PHP_81
    ]);

    // Define sets of rules
    $rectorConfig->sets([
        SetList::PHP_81,
    ]);
};
