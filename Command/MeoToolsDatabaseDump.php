<?php

declare(strict_types=1);

namespace Acquia\MeoTools\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: self::NAME,
    description: 'Dump the database for all sites in a multi-site Drupal installation.',
    aliases: ['sfdu', 'meo-tools-dump'],
    usages: [
        'meo-tools:dump',
        'meo-tools:dump --result-folder=/home/project/backup/1.0.9',
        'meo-tools:dump --result-folder=/home/project/backup/20160617 --gzip',
    ],
)]
class MeoToolsDatabaseDump {
  use MeoToolsUtilsTrait;

  public const string NAME = 'meo-tools:dump';

  private SymfonyStyle $io;

  public function __invoke(
      InputInterface $input,
      OutputInterface $output,
      #[Option(name: 'result-folder', description: 'The folder in which the backups will be written. Defaults to ~/drush-backups/[YYYYmmdd-hhmm].')]
      string $result_folder = '',
      #[Option(description: 'Whether to gzip the backup files.')]
      bool $gzip = FALSE,
  ): int {
    $this->io = new SymfonyStyle($input, $output);

    $options = $input->getOptions();
    // Use the default result folder if not provided.
    if (empty($options['result-folder'])) {
      $options['result-folder'] = $_SERVER['HOME'] . '/drush-backups/' . date('Ymd-Hi');
    }

    // Ask for confirmation before running the command.
    if (!$this->io->confirm("Are you sure you want to take database dumps of all the sites of the factory in {$options['result-folder']}.")) {
      $this->io->warning("Operation cancelled by user.");
      return Command::FAILURE;
    }

    // If dump directory does not exist.
    $backup_result_folder = $options['result-folder'];
    if(!file_exists($backup_result_folder)){
      if (!$this->io->confirm("Target directory ($backup_result_folder) does not exist. Do you want to create this directory?")) {
        $this->io->warning("Operation cancelled by user.");
        return Command::FAILURE;
      }

      $this->io->writeln("=> Creating $backup_result_folder directory ...");
      // Create dump directory.
      if (!mkdir($backup_result_folder, 0755, TRUE) || !file_exists($backup_result_folder)) {
        $this->io->error("Unable to create the dump directory $backup_result_folder.");
        return Command::FAILURE;
      }

      $this->io->success("Folder $backup_result_folder has been created.");
    }

    // Look for list of sites and loop over it.
    if ($sites = $this->getMultiSiteSites()) {
      foreach ($sites as $details) {
        $domain = $details['domains'][0];

        $command_options = [];
        $result_file = $backup_result_folder . '/' . $details['machine_name'] . '.sql';
        $command_options['result-file'] = $result_file;
        $command_options['uri'] = $domain;

        $process_args = [
          'dr',
          'sql-dump',
//          '--uri=' . $domain, // URI option no longer available and thus need the database and target key information
          '--result-file=' . $command_options['result-file'],
        ];

        if ($gzip) {
          $process_args[] = '--gzip';
        }

        $this->io->writeln("=> Taking database dump of {$details['machine_name']} ...");
        $process = new Process($process_args);
        $process->setTimeout(NULL); // Database dumps may take a long time.

        $exit_code = $process->run();
        if ($exit_code !== 0) {
          // Throw an exception with details about the failed process.
          $this->io->error("The command failed to execute for the site {$details['machine_name']}.");
          $this->io->writeln($process->getErrorOutput());
          continue;
        }

        // Log success message.
        if ($gzip) {
          $result_file .= '.gz';
        }
        $this->io->success("Database dump of {$details['machine_name']} site successfully saved in {$result_file}.");
      }
    }

    return Command::SUCCESS;
  }

}
