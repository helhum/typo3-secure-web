<?php
declare(strict_types=1);
namespace Helhum\Typo3SecureWeb\Composer;

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
use Helhum\Typo3ComposerSetup\Composer\InstallerScript\RootDirectory;
use Helhum\Typo3SecureWeb\Composer\InstallerScript\DummyEntryPoints;
use Helhum\Typo3SecureWeb\Composer\InstallerScript\WebDirectory;
use TYPO3\CMS\Composer\Plugin\Config;
use TYPO3\CMS\Composer\Plugin\Core\InstallerScriptsRegistration;
use TYPO3\CMS\Composer\Plugin\Core\ScriptDispatcher;

/**
 * Hook into Composer build to set up TYPO3 web directory if necessary
 */
class InstallerScripts implements InstallerScriptsRegistration
{
    /**
     * @param Event $event
     * @param ScriptDispatcher $scriptDispatcher
     */
    public static function register(Event $event, ScriptDispatcher $scriptDispatcher)
    {
        $composer = $event->getComposer();
        $pluginConfig = Config::load($composer);
        $rootDir = $pluginConfig->get('root-dir');
        $webDir = $pluginConfig->get('web-dir');
        $typo3CmsPackage = $event->getComposer()->getRepositoryManager()->getLocalRepository()->findPackage('typo3/cms', new EmptyConstraint());

        if ($typo3CmsPackage && !class_exists(\Helhum\Typo3NoSymlinkInstall\Composer\InstallerScripts::class)) {
            $scriptDispatcher->addInstallerScript(
                new RootDirectory($rootDir, RootDirectory::PUBLISH_STRATEGY_LINK),
                90
            );
        }
        if ($rootDir !== $webDir) {
            $scriptDispatcher->addInstallerScript(
                new WebDirectory(),
                70
            );
            $scriptDispatcher->addInstallerScript(
                new DummyEntryPoints(),
                70
            );
        } else {
            $event->getIO()->writeError('<warning>The config option web-dir and root-dir are set to the same value. Skipped web directory setup.</warning>');
        }
    }
}
