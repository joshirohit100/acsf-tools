<?php

declare(strict_types=1);

namespace Acquia\MeoTools\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: self::NAME,
    description: 'List all sites in a multi-site Drupal installation.',
    aliases: ['sfl', 'meo-tools-list'],
    usages: [
        'meo-tools:list',
        'meo-tools:list --fields',
        'meo-tools:list --fields=name,domains',
    ],
)]
class MeoToolsSiteList {
  use MeoToolsUtilsTrait;

  public const string NAME = 'meo-tools:list';

  private SymfonyStyle $io;

  public function __invoke(
      InputInterface $input,
      OutputInterface $output,
      #[Option(description: 'The list of fields to display (comma separated list).')]
      string $fields = '',
  ): int {
    $this->io = new SymfonyStyle($input, $output);

    // Look for list of sites and loop over it.
    if ($sites = $this->getMultiSiteSites()) {
      $options = $input->getOptions();
      $fields = $options['fields'];
      if (!empty($fields)) {
        $expected_attributes = array_flip(explode(',', $fields));
      }

      foreach ($sites as $site) {
        $prefix = $site['machine_name'];
        $this->io->writeln($prefix);

        if (isset($expected_attributes)) {
          $site = array_intersect_key($site, $expected_attributes);
        }

        $this->recursivePrint($site, 2);
      }
    }

    return Command::SUCCESS;
  }

}
