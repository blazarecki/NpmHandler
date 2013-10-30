<?php

/*
 * This file is part of the NpmHandler package.
 *
 * (c) Benjamin Lazarecki <benjamin.lazarecki@gmail.com>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Scar\NpmHandler\Composer;

use Composer\Script\Event;
use Scar\NpmHandler\Npm\NpmManager;

/**
 * Npm handler.
 *
 * @author Benjamin Lazarecki <benjamin.lazarecki@gmail.com>
 */
class NpmHandler
{
    /**
     * Install the bower packages.
     *
     * @param \Composer\Script\Event $event The handler event.
     */
    public static function install(Event $event)
    {
        $npmManager = new NpmManager();
        $npmManager->setOutput($event->getIO());

        $extra = $event->getComposer()->getPackage()->getExtra();

        $npmManager->process(
            getcwd(),
            $event->isDevMode(),
            $event->getIO()->isVerbose(),
            self::getNpmExecutablePath($extra),
            self::getExcludedDirectories($extra)
        );
    }

    /**
     * Gets the excluded directories.
     *
     * @param array $extra The composer extra configuration.
     *
     * @return array The excluded directories.
     */
    protected static function getExcludedDirectories(array $extra)
    {
        if (isset($extra['npm-handler']) && isset($extra['npm-handler']['exclude-packages'])) {
            return (array) $extra['npm-handler']['exclude-packages'];
        }

        return array();
    }

    /**
     * Gets the path to npm executable.
     *
     * @param array $extra The composer extra configuration.
     *
     * @return null|string The path to npm executable if it's in extra parameters.
     */
    protected static function getNpmExecutablePath(array $extra)
    {
        if (isset($extra['npm-handler']) && isset($extra['npm-handler']['npm-path'])) {
            return $extra['npm-handler']['npm-path'];
        }

        return null;
    }
}
