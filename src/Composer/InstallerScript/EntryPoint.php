<?php
declare(strict_types=1);
namespace Helhum\Typo3SecureWeb\Composer\InstallerScript;

use Composer\Script\Event;
use Composer\Util\Filesystem;
use TYPO3\CMS\Composer\Plugin\Core\InstallerScript;

class EntryPoint implements InstallerScript
{
    /**
     * Absolute path to entry script source
     *
     * @var string
     */
    private $source;

    /**
     * The target file relative to the web directory
     *
     * @var string
     */
    private $target;

    /**
     * @var string
     */
    private $additionalCode;

    public function __construct(string $source, string $target, string $additionalCode = '')
    {
        $this->source = $source;
        $this->target = $target;
        $this->additionalCode = $additionalCode;
    }

    public function run(Event $event): bool
    {
        $composer = $event->getComposer();
        $filesystem = new Filesystem();

        $entryPointContent = file_get_contents($this->source);
        $autoloadFile = $composer->getConfig()->get('vendor-dir') . '/autoload.php';

        $entryPointContent = preg_replace(
            '/(\$classLoader = require )[^;]*;$/m',
            '\1' . $filesystem->findShortestPathCode($this->target, $autoloadFile) . ';'
            . chr(10)
            . $this->additionalCode,
            $entryPointContent
        );

        $filesystem->ensureDirectoryExists(dirname($this->target));
        file_put_contents($this->target, $entryPointContent);

        return true;
    }
}
