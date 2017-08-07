<?php
declare(strict_types=1);
namespace Helhum\Typo3NoSymlinkInstall\Composer\InstallerScripts;

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
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Script\Event;
use Composer\Semver\Constraint\EmptyConstraint;
use TYPO3\CMS\Composer\Plugin\Config;
use TYPO3\CMS\Composer\Plugin\Core\InstallerScript;
use TYPO3\CMS\Composer\Plugin\Util\Filesystem;

/**
 * Setting up TYPO3 web directory
 */
class WebDirectory implements InstallerScript
{
    /**
     * @var string
     */
    private static $typo3Dir = '/typo3';

    /**
     * @var string
     */
    private static $systemExtensionsDir = '/sysext';

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Config
     */
    private $pluginConfig;

    /**
     * @var bool
     */
    private $isDevMode = false;

    /**
     * Prepare the web directory with symlinks
     *
     * @param Event $event
     * @return bool
     */
    public function run(Event $event): bool
    {
        $this->io = $event->getIO();
        $this->composer = $event->getComposer();
        $this->filesystem = new Filesystem();
        $this->pluginConfig = Config::load($this->composer);
        $this->isDevMode = $event->isDevMode();

        $webDir = $this->filesystem->normalizePath($this->pluginConfig->get('web-dir'));
        $backendDir = $webDir . self::$typo3Dir;
        $this->filesystem->ensureDirectoryExists($backendDir);

        $localRepository = $this->composer->getRepositoryManager()->getLocalRepository();
        $typo3Package = $localRepository->findPackage('typo3/cms', new EmptyConstraint());
        $sourcesDir = $this->composer->getInstallationManager()->getInstallPath($typo3Package);

        $source = $sourcesDir . self::$typo3Dir . self::$systemExtensionsDir;
        $target = $backendDir . self::$systemExtensionsDir;

        if (is_dir($target)) {
            $this->filesystem->removeDirectory($target);
        }
        foreach ($this->getCoreExtensionKeysFromTypo3Package($typo3Package) as $coreExtKey) {
            $this->filesystem->copy($source . '/' . $coreExtKey, $target . '/' . $coreExtKey);
        }

        return true;
    }


    /**
     * @param PackageInterface $typo3Package
     * @return array
     */
    private function getCoreExtensionKeysFromTypo3Package(PackageInterface $typo3Package): array
    {
        $coreExtensionKeys = [];
        $frameworkPackages = [];
        foreach ($typo3Package->getReplaces() as $name => $_) {
            if (strpos($name, 'typo3/cms-') === 0) {
                $frameworkPackages[] = $name;
            }
        }
        $installedPackages = $this->composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages();
        $rootPackage = $this->composer->getPackage();
        $installedPackages[$rootPackage->getName()] = $rootPackage;
        foreach ($installedPackages as $package) {
            $requires = $package->getRequires();
            if ($package === $rootPackage && $this->isDevMode) {
                $requires = array_merge($requires, $package->getDevRequires());
            }
            foreach ($requires as $name => $link) {
                if (in_array($name, $frameworkPackages, true)) {
                    $extensionKey = $this->determineExtKeyFromPackageName($name);
                    $this->io->writeError(sprintf('The package "%s" requires: "%s"', $package->getName(), $name), true, IOInterface::DEBUG);
                    $this->io->writeError(sprintf('The extension key for package "%s" is: "%s"', $name, $extensionKey), true, IOInterface::DEBUG);
                    $coreExtensionKeys[$name] = $extensionKey;
                }
            }
        }
        return $coreExtensionKeys;
    }

    /**
     * @param string $packageName
     * @return string
     */
    private function determineExtKeyFromPackageName(string $packageName): string
    {
        return str_replace(['typo3/cms-', '-'], ['', '_'], $packageName);
    }
}
