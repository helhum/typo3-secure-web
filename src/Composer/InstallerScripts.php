<?php
declare(strict_types=1);
namespace Helhum\Typo3NoSymlinkInstall\Composer;

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

use Composer\Script\Event;
use Composer\Semver\Constraint\EmptyConstraint;
use Helhum\Typo3NoSymlinkInstall\Composer\InstallerScripts\EntryPoint;
use Helhum\Typo3NoSymlinkInstall\Composer\InstallerScripts\Stop;
use Helhum\Typo3NoSymlinkInstall\Composer\InstallerScripts\WebDirectory;
use TYPO3\CMS\Composer\Plugin\Core\InstallerScriptsRegistration;
use TYPO3\CMS\Composer\Plugin\Core\ScriptDispatcher;

/**
 * Hook into Composer build to set up TYPO3 web directory if necessary
 */
class InstallerScripts implements InstallerScriptsRegistration
{
    private static $entryPoints = [
        'frontend' => [
            'target' => 'index.php',
        ],
        'backend' => [
            'target' => 'typo3/index.php',
        ],
        'install' => [
            'target' => 'typo3/install.php',
        ],
    ];

    /**
     * @param Event $event
     * @param ScriptDispatcher $scriptDispatcher
     */
    public static function register(Event $event, ScriptDispatcher $scriptDispatcher)
    {
        $typo3CmsPackage = $event->getComposer()->getRepositoryManager()->getLocalRepository()->findPackage('typo3/cms', new EmptyConstraint());
        $cmsInstallPath = $event->getComposer()->getInstallationManager()->getInstallPath($typo3CmsPackage);
        self::determineEntryScriptSourceFiles($cmsInstallPath);

        $scriptDispatcher->addInstallerScript(
            new WebDirectory(),
            80
        );
        foreach (self::$entryPoints as $entryPoint) {
            $scriptDispatcher->addInstallerScript(
                new EntryPoint(
                    $entryPoint['source'],
                    $entryPoint['target']
                ),
                70
            );

        }
        $scriptDispatcher->addInstallerScript(
            new Stop(),
            60
        );
    }

    private static function determineEntryScriptSourceFiles(string $cmsInstallPath)
    {
        if (file_exists($frontendSourceFile = $cmsInstallPath . '/typo3/sysext/frontend/Resources/Private/Php/frontend.php')) {
            self::$entryPoints['frontend']['source'] = $frontendSourceFile;
        } else {
            self::$entryPoints['frontend']['source'] = $cmsInstallPath . '/index.php';
        }

        if (file_exists($backendSourceFile = $cmsInstallPath . '/typo3/sysext/backend/Resources/Private/Php/backend.php')) {
            self::$entryPoints['backend']['source'] = $backendSourceFile;
        } else {
            self::$entryPoints['backend']['source'] = $cmsInstallPath . '/typo3/index.php';
        }

        if (file_exists($installSourceFile = $cmsInstallPath . '/typo3/sysext/install/Resources/Private/Php/install.php')) {
            self::$entryPoints['install']['source'] = $installSourceFile;
        } else {
            self::$entryPoints['install']['source'] = $cmsInstallPath . '/typo3/sysext/install/Start/Install.php';
            self::$entryPoints['install']['target'] = 'typo3/sysext/install/Start/Install.php';
        }
    }
}
