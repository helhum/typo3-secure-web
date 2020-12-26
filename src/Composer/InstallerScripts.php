<?php
declare(strict_types=1);
namespace Helhum\Typo3SecureWeb\Composer;

use Composer\Script\Event;
use Helhum\Typo3SecureWeb\Composer\InstallerScript\DummyEntryPoints;
use Helhum\Typo3SecureWeb\Composer\InstallerScript\EntryPoint;
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
        if ($rootDir === $webDir) {
            $event->getIO()->writeError('<warning>The config option web-dir and root-dir are set to the same value. Skipped web directory setup.</warning>');
            return;
        }
        $entryPointFinder = new Typo3EntryPointFinder(
            $composer->getRepositoryManager()->getLocalRepository(),
            $composer->getInstallationManager()
        );
        foreach ($entryPointFinder->find($webDir) as $entryPoint) {
            $scriptDispatcher->addInstallerScript(
                new EntryPoint(
                    $entryPoint['source'],
                    $entryPoint['target'],
                    '    chdir(getenv(\'TYPO3_PATH_ROOT\'));'
                ),
                40
            );
        }

        $scriptDispatcher->addInstallerScript(
            new WebDirectory(),
            70
        );
        $scriptDispatcher->addInstallerScript(
            new DummyEntryPoints(),
            70
        );
    }
}
