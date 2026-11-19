<?php

declare(strict_types=1);

namespace Acquia\MeoTools;

use Acquia\MeoTools\Command\MeoToolsSiteList;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;
use Symfony\Component\DependencyInjection\Definition;

class MeoToolsServiceProvider implements ServiceProviderInterface {

  public function register(ContainerBuilder $container): void {
    $definition = (new Definition(MeoToolsSiteList::class))
        ->setAutowired(true)
        ->setPublic(true)
        ->addTag('console.command');
    $container->setDefinition(MeoToolsSiteList::class, $definition);
  }

}
