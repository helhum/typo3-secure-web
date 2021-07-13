<?php
declare(strict_types=1);
namespace Helhum\Typo3SecureWeb\Composer;

use Composer\Installer\InstallationManager;
use Composer\Repository\WritableRepositoryInterface;

class Typo3EntryPointFinder
{
    private const defaultEntryPoints = [
        'frontend' => [
            'source' => '/Resources/Private/Php/frontend.php',
            'target' => '/index.php',
        ],
        'backend' => [
            'source' => '/Resources/Private/Php/backend.php',
            'target' => '/typo3/index.php',
        ],
        'install' => [
            'source' => '/Resources/Private/Php/install.php',
            'target' => '/typo3/install.php',
        ],
    ];

    /**
     * @var WritableRepositoryInterface
     */
    private $repository;

    /**
     * @var InstallationManager
     */
    private $installationManager;

    public function __construct(WritableRepositoryInterface $repository, InstallationManager $installationManager)
    {
        $this->repository = $repository;
        $this->installationManager = $installationManager;
    }

    public function find(string $targetPath): array
    {
        $entryPoints = [];
        if ($frontendPackage = $this->repository->findPackage('typo3/cms-frontend', '*')) {
            $entryPoints['frontend']['source'] = $this->installationManager->getInstallPath($frontendPackage) . self::defaultEntryPoints['frontend']['source'];
            $entryPoints['frontend']['target'] = $targetPath . self::defaultEntryPoints['frontend']['target'];
        }
        if ($backendPackage = $this->repository->findPackage('typo3/cms-backend', '*')) {
            $entryPoints['backend']['source'] = $this->installationManager->getInstallPath($backendPackage) . self::defaultEntryPoints['backend']['source'];
            $entryPoints['backend']['target'] = $targetPath . self::defaultEntryPoints['backend']['target'];
        }
        if ($installPackage = $this->repository->findPackage('typo3/cms-install', '*')) {
            $entryPoints['install']['source'] = $this->installationManager->getInstallPath($installPackage) . self::defaultEntryPoints['install']['source'];
            $entryPoints['install']['target'] = $targetPath . self::defaultEntryPoints['install']['target'];
        }

        return $entryPoints;
    }
}
