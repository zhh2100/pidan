<?php
declare (strict_types = 1);

namespace pidan\console\command;

use pidan\console\Command;
use pidan\console\Input;
use pidan\console\Output;

class ServiceDiscover extends Command
{
    public function configure()
    {
        $this->setName('service:discover')
            ->setDescription('Discover Services for PidanPHP');
    }

    public function execute(Input $input, Output $output)
    {
        if (is_file($path = $this->app->getRootPath() . 'vendor/composer/installed.json')) {
            $packages = json_decode(@file_get_contents($path), true);
            // Compatibility with Composer 2.0
            if (isset($packages['packages'])) {
                $packages = $packages['packages'];
            }

            $services = [];
            foreach ($packages as $package) {
                if (!empty($package['extra']['pidan']['services'])) {
                    $services = array_merge($services, (array) $package['extra']['pidan']['services']);
                }
            }

            $header = '// This file is automatically generated at:' . date('Y-m-d H:i:s') . PHP_EOL . 'declare (strict_types = 1);' . PHP_EOL;

            $content = '<?php ' . PHP_EOL . $header . "return " . var_export($services, true) . ';';

            file_put_contents($this->app->getRootPath() . 'vendor/services.php', $content);

            $output->writeln('<info>Succeed!</info>');
        }
    }
}
