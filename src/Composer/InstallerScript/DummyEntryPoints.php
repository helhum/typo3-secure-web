<?php
declare(strict_types=1);
namespace Helhum\Typo3SecureWeb\Composer\InstallerScript;

/*
 * This file is part of the TYPO3 project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Composer\Composer;
use Composer\Script\Event;
use TYPO3\CMS\Composer\Plugin\Config;
use TYPO3\CMS\Composer\Plugin\Core\InstallerScript;

/**
 * Setting up dummy entry points for TYPO3 root directory
 */
class DummyEntryPoints implements InstallerScript
{
    /**
     * Prepare the root directory with dummy entry points
     *
     * The "real" entry points are in the web directory.
     *
     * @param Event $event
     * @return bool
     */
    public function run(Event $event): bool
    {
        $composer = $event->getComposer();
        $pluginConfig = Config::load($composer);
        $rootDir = $pluginConfig->get('root-dir');
        $webDir = $pluginConfig->get('web-dir');

        $dummyEntryPoints = [
            '/index.php',
            '/typo3/index.php',
            '/typo3/install.php',
        ];

        foreach ($dummyEntryPoints as $dummyEntryPoint) {
            if (file_exists($webDir . $dummyEntryPoint)) {
                file_put_contents($rootDir . $dummyEntryPoint, '');
            }
        }

        return true;
    }
}
