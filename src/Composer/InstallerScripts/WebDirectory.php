<?php
declare(strict_types=1);
namespace Helhum\Typo3SecureWeb\Composer\InstallerScripts;

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
use Composer\Util\Platform;
use TYPO3\CMS\Composer\Plugin\Config;
use TYPO3\CMS\Composer\Plugin\Core\InstallerScript;
use TYPO3\CMS\Composer\Plugin\Util\ExtensionKeyResolver;
use TYPO3\CMS\Composer\Plugin\Util\Filesystem;

/**
 * Setting up TYPO3 web directory (link public assets)
 */
class WebDirectory implements InstallerScript
{
    /**
     * @var string
     */
    private static $systemExtensionsDir = '/typo3/sysext/';

    /**
     * @var string
     */
    private static $extensionsDir = '/typo3conf/ext/';

    /**
     * @var string
     */
    private static $resourcesDir = '/Resources/Public';

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

        foreach ($this->getInstalledExtensions() as $paths) {
            if (!is_dir($paths['source'])) {
                continue;
            }
            $this->linkDir($paths['source'], $paths['target']);
        }
        $webDir = $this->filesystem->normalizePath($this->pluginConfig->get('web-dir'));
        $rootDir = $this->filesystem->normalizePath($this->pluginConfig->get('root-dir'));

        $links = [
            'fileadmin',
            'typo3temp/assets'
        ];

        foreach ($links as $link) {
            $this->linkDir($rootDir . '/' . $link, $webDir . '/' . $link);
        }

        return true;
    }

    private function linkDir($source, $target)
    {
        $fileSystem = new \Symfony\Component\Filesystem\Filesystem();
        if (Platform::isWindows()) {
            // Implement symlinks as NTFS junctions on Windows
            $this->filesystem->junction($source, $target);
        } else {
            $absolutePath = $target;
            if (!$this->filesystem->isAbsolutePath($absolutePath)) {
                $absolutePath = getcwd() . DIRECTORY_SEPARATOR . $target;
            }
            $shortestPath = $this->filesystem->findShortestPath($absolutePath, $source);
            $target = rtrim($target, '/');
            $fileSystem->symlink($shortestPath, $target);
        }
    }

    private function getInstalledExtensions()
    {
        $installedPackages = $this->composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages();
        $installedExtensions = [];
        $webDir = $this->filesystem->normalizePath($this->pluginConfig->get('web-dir'));
        foreach ($installedPackages as $package) {
            if (in_array($package->getType(), ['typo3-cms-extension', 'typo3-cms-framework'], true)) {
                $extKey = ExtensionKeyResolver::resolve($package);
                $installedExtensions[$extKey]['source'] = $this->composer->getInstallationManager()->getInstallPath($package) . self::$resourcesDir;
                $installedExtensions[$extKey]['target'] = $webDir . self::$extensionsDir . $extKey . self::$resourcesDir;
                if ($package->getType() === 'typo3-cms-framework') {
                    $installedExtensions[$extKey]['target'] = $webDir . self::$systemExtensionsDir . $extKey . self::$resourcesDir;
                }
            }
            if ($package->getType() === 'typo3-cms-core') {
                $coreInstallPath = $this->composer->getInstallationManager()->getInstallPath($package);
                foreach ($this->getCoreExtensionKeysFromTypo3Package($package) as $coreExtKey) {
                    $installedExtensions[$coreExtKey]['source'] = $coreInstallPath . self::$systemExtensionsDir . $coreExtKey . self::$resourcesDir;
                    $installedExtensions[$coreExtKey]['target'] = $webDir . self::$systemExtensionsDir . $coreExtKey . self::$resourcesDir;
                }
            }
        }
        return $installedExtensions;
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
            if (is_string($name) && strpos($name, 'typo3/cms-') === 0) {
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
            foreach ($requires as $name => $_) {
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
