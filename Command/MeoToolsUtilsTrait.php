<?php

declare(strict_types=1);

namespace Acquia\MeoTools\Command;

trait MeoToolsUtilsTrait {

  /**
   * Utility function to retrieve a list of sites in a multi-site Drupal installation.
   *
   * @return array
   * @throws \RuntimeException
   */
  public function getMultiSiteSites(): array {
    // Read information from the sites.php file inside `/sites` directory.
    // @todo: Change this to proper file where data is available.
    $multisite_directory_path = DRUPAL_ROOT . '/sites';
    $multisite_file_path = $multisite_directory_path.'/sites.php';

    if (!is_file($multisite_file_path)) {
      throw new \RuntimeException("Cannot find $multisite_file_path");
    }

    require $multisite_file_path;

    if (!isset($sites) || !is_array($sites)) {
      throw new \RuntimeException("Multi-site not defined in $multisite_file_path");
    }

    // Sort to ensure technical domains are first.
    uksort(
      $sites,
      function (string $a, string $b): int {
        return !str_ends_with($a, '.acquia-sites.com') <=> !str_ends_with($b, '.acquia-sites.com');
      },
    );

    $site_details = [];
    foreach ($sites as $site_name => $site_path) {
      // If site path already exists, just add the domain to the list of domains.
      if (!empty($site_details[$site_path])) {
        $site_details[$site_path]['domains'][] = $site_name;
        continue;
      }

      $site_details[$site_path] = [
        'name' => $site_path,
        'domains' => [
          $site_name,
        ],
        'conf' => [
          'db_name' => $site_path, // Default to site name as DB name.
          'site_id' => $site_path, // Default to site name as site ID.
        ],
        'machine_name' => $site_path, // Default to site name as machine name.
      ];
    }

    return $site_details;
  }

  /**
   * Utility function to recursively pretty print arrays for drush.
   *
   * @param $variable
   * @param $indent
   */
  private function recursivePrint($variable, $indent) {
    $tab = str_repeat(' ', $indent);

    foreach ($variable as $key => $value) {
      if (!is_array($value)) {
        $this->io->writeln($tab . $key . ': ' . $value);
      }
      else {
        $this->io->writeln($tab . $key . ':');
        $this->recursivePrint($value, $indent + 2);
      }
    }
  }

}
