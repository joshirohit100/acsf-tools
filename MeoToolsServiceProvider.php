<?php

declare(strict_types=1);

namespace Acquia\MeoTools;

use Acquia\MeoTools\Command\MeoToolsSiteList;
use Acquia\MeoTools\Command\MeoToolsDatabaseDump;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;
use Symfony\Component\DependencyInjection\Definition;

class MeoToolsServiceProvider implements ServiceProviderInterface {

  public function register(ContainerBuilder $container): void {
    $commands = [
      MeoToolsSiteList::class,
      MeoToolsDatabaseDump::class,
    ];

    foreach ($commands as $command) {
      $definition = (new Definition($command))
        ->setAutowired(true)
        ->setPublic(true)
        ->addTag('console.command');
      $container->setDefinition($command, $definition);
    }
  }

}
