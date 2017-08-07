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

use Composer\Script\Event;
use Composer\Util\Filesystem;
use TYPO3\CMS\Composer\Plugin\Config;
use TYPO3\CMS\Composer\Plugin\Core\InstallerScript;
use TYPO3\CMS\Composer\Plugin\Core\StopInstallerScriptExecution;

class Stop implements InstallerScript
{
    public function run(Event $event): bool
    {
        throw new StopInstallerScriptExecution('Avoid executing TYPO3 scripts');
    }
}
