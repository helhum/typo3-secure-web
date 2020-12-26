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
        $frontendPackage = $this->repository->findPackage('typo3/cms-frontend', '*');
        $frontendPackagePath = $this->installationManager->getInstallPath($frontendPackage);
        $backendPackage = $this->repository->findPackage('typo3/cms-backend', '*');
        $backendPackagePath = $this->installationManager->getInstallPath($backendPackage);
        $installPackage = $this->repository->findPackage('typo3/cms-install', '*');
        $installPackagePath = $this->installationManager->getInstallPath($installPackage);

        $entryPoints['frontend']['source'] = $frontendPackagePath . self::defaultEntryPoints['frontend']['source'];
        $entryPoints['backend']['source'] = $backendPackagePath . self::defaultEntryPoints['backend']['source'];
        $entryPoints['install']['source'] = $installPackagePath . self::defaultEntryPoints['install']['source'];

        $entryPoints['frontend']['target'] = $targetPath . self::defaultEntryPoints['frontend']['target'];
        $entryPoints['backend']['target'] = $targetPath . self::defaultEntryPoints['backend']['target'];
        $entryPoints['install']['target'] = $targetPath . self::defaultEntryPoints['install']['target'];

        return $entryPoints;
    }
}
