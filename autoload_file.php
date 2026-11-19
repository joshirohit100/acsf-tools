<?php

declare(strict_types=1);

use Acquia\MeoTools\MeoToolsServiceProvider;

// This allows PHP code to add service providers to Drupal core.
$GLOBALS['conf']['container_service_providers']['MeoToolsServiceProvider'] = MeoToolsServiceProvider::class;
