<?php
declare(strict_types=1);
namespace Helhum\Typo3SecureWeb\Composer\InstallerScript;

use Composer\Script\Event;
use TYPO3\CMS\Composer\Plugin\Config;
use TYPO3\CMS\Composer\Plugin\Core\InstallerScript;

/**
 * Setting up dummy entry points for TYPO3 root directory
 */
class DummyEntryPoints implements InstallerScript
{
    private const dummyPhpCode = '<?php die(\'Called TYPO3 from the wrong document root!\');';

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

        $dummyEntryPoints = [
            '/index.php',
            '/typo3/index.php',
            '/typo3/install.php',
        ];

        foreach ($dummyEntryPoints as $dummyEntryPoint) {
            file_put_contents($rootDir . $dummyEntryPoint, self::dummyPhpCode);
        }

        return true;
    }
}
