<?php
declare(strict_types=1);
namespace Helhum\Typo3SecureWeb\Composer\InstallerScript;

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
    private const systemExtensionsDir = '/typo3/sysext/';

    /**
     * @var string
     */
    private const extensionsDir = '/typo3conf/ext/';

    /**
     * @var string
     */
    private const resourcesDir = '/Resources/Public';

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

        $links = $this->getLinksFromInstalledExtensions();
        $links = array_merge($links, $this->getCoreLinks());

        foreach ($links as $paths) {
            if (!is_dir($paths['source']) || is_dir($paths['target'])) {
                continue;
            }
            $this->linkDir($paths['source'], $paths['target']);
        }

        $this->removeStaleLinks($links);

        return true;
    }

    private function getCoreLinks(): array
    {
        $links = [];

        $coreLinks = [
            'fileadmin',
            'typo3temp/assets',
        ];

        $webDir = $this->filesystem->normalizePath($this->pluginConfig->get('web-dir'));
        $rootDir = $this->filesystem->normalizePath($this->pluginConfig->get('root-dir'));

        foreach ($coreLinks as $link) {
            $source = $rootDir . '/' . $link;
            $target = $webDir . '/' . $link;
            try {
                $this->filesystem->ensureDirectoryExists($source);
                $links[] = [
                    'source' => $source,
                    'target' => $target,
                ];
            } catch (\Throwable $e) {
                $this->io->writeError(sprintf('<warning>Could not create directory "%s". Symlink in document root will not be created.</warning>', $link));
            }
        }

        return $links;
    }

    private function getLinksFromInstalledExtensions(): array
    {
        $installedPackages = $this->composer->getRepositoryManager()->getLocalRepository()->getCanonicalPackages();
        $installedExtensions = [];
        $webDir = $this->filesystem->normalizePath($this->pluginConfig->get('web-dir'));
        foreach ($installedPackages as $package) {
            if (in_array($package->getType(), ['typo3-cms-extension', 'typo3-cms-framework'], true)) {
                $extensionBaseDir = self::extensionsDir;
                if ($package->getType() === 'typo3-cms-framework') {
                    $extensionBaseDir = self::systemExtensionsDir;
                }
                $extKey = ExtensionKeyResolver::resolve($package);
                $installedExtensions[$extKey]['source'] = $this->composer->getInstallationManager()->getInstallPath($package) . self::resourcesDir;
                $installedExtensions[$extKey]['target'] = $webDir . $extensionBaseDir . $extKey . self::resourcesDir;
            }
        }
        return $installedExtensions;
    }

    private function removeStaleLinks(array $currentLinks)
    {
        $currentTargets = [];
        foreach ($currentLinks as $currentLink) {
            $currentTargets[] = $this->filesystem->normalizePath($currentLink['target']);
        }
        $targetDir = $this->pluginConfig->get('web-dir');
        $currentExtensionDirs = glob($targetDir . '/typo3conf/ext/*' . self::resourcesDir);
        $currentSystemExtensionDirs = glob($targetDir . '/typo3/sysext/*' . self::resourcesDir);

        $fileSystem = new \Symfony\Component\Filesystem\Filesystem();
        foreach (array_merge($currentExtensionDirs, $currentSystemExtensionDirs) as $dir) {
            $dir = $this->filesystem->normalizePath($dir);
            if (!in_array($dir, $currentTargets, true)) {
                if ($this->filesystem->isJunction($dir)) {
                    $this->filesystem->removeJunction($dir);
                }
                $fileSystem->remove(str_replace(self::resourcesDir, '', $dir));
            }
        }
    }

    private function linkDir($source, $target)
    {
        $fileSystem = new \Symfony\Component\Filesystem\Filesystem();
        if (Platform::isWindows()) {
            $this->filesystem->ensureDirectoryExists(dirname($target));
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
}
