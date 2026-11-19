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

  public function __invoke(
      InputInterface $input,
      OutputInterface $output,
      #[Option(description: 'The list of fields to display (comma separated list).')]
      string $fields,
  ): int {
    $io = new SymfonyStyle($input, $output);

    // Look for list of sites and loop over it.
    if ($sites = $this->getSites()) {
      $options = $input->getOptions();
      $fields = $options['fields'];
      if (isset($fields)) {
        $expected_attributes = array_flip(explode(',', $fields));
      }

      foreach ($sites as $name => $details) {
        $prefix = $details['machine_name'];
        $io->writeln($prefix);

        // Filter attributes.
        if (isset($expected_attributes)) {
          $details = array_intersect_key($details, $expected_attributes);
        }

        // Print attributes.
        $this->recursivePrint($details, 2);
      }
    }

    return Command::SUCCESS;
  }

}
