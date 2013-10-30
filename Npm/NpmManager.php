<?php

/*
 * This file is part of the NpmHandler package.
 *
 * (c) Benjamin Lazarecki <benjamin.lazarecki@gmail.com>
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code.
 */

namespace Scar\NpmHandler\Npm;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

/**
 * Npm manager.
 *
 * @author Benjamin Lazarecki <benjamin.lazarecki@gmail.com>
 */
class NpmManager
{
    /** @var mixed */
    private $output;

    /**
     * Checks if the npm manager has an output.
     *
     * @return boolean TRUE if the npm manager has an output else FALSE.
     */
    protected function hasOutput()
    {
        return $this->output !== null;
    }

    /**
     * Gets the output.
     *
     * @return mixed The output.
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Sets the output.
     *
     * @param mixed $output The output.
     */
    public function setOutput($output = null)
    {
        $this->output = $output;
    }

    /**
     * Processes the npm installation.
     *
     * @param string      $rootPath      The npm root path.
     * @param boolean     $devMode       TRUE if the manager is in dev mode, else FALSE.
     * @param boolean     $verbose       TRUE if the manager is verbose else FALSE.
     * @param null|string $npmExecutable Path to the npm executable
     * @param array       $excludes      The paths to exclude.
     */
    public function process($rootPath, $devMode, $verbose = false, $npmExecutable = null, array $excludes = array())
    {
        $this->write('<info>NPM Components</info>');

        try {
            $npmExecutable = $this->getNpmExecutablePath($npmExecutable, $rootPath);
        } catch (\InvalidArgumentException $exception) {
            $this->write($exception->getMessage());

            return;
        }

        $that = $this;

        foreach ($this->resolvePackagesPaths($rootPath, $excludes) as $path) {
            $name = str_replace($rootPath.DIRECTORY_SEPARATOR, '', $path);
            $this->write(sprintf('- Installing <comment>%s</comment>', $name));

            $process = new Process(
                sprintf(
                    'cd %s && %s install %s',
                    escapeshellarg(dirname($path)),
                    $npmExecutable,
                    $devMode ? null : '--production'
                )
            );

            $process->run(function ($type, $buffer) use ($that, $verbose) {
                if ($verbose) {
                    $that->write($type === 'err' ? sprintf('<error>%s</error>', $buffer) : $buffer, false);
                }
            });

            if (!$process->isSuccessful()) {
                $this->write(sprintf('<error>%s</error>', $process->getErrorOutput()), false);
            }
        }
    }

    /**
     * Writes on the output.
     *
     * @param string  $messages The messages.
     * @param boolean $newline  TRUE if there is a new line else FALSE.
     */
    public function write($messages, $newline = true)
    {
        if ($this->hasOutput()) {
            $this->getOutput()->write($messages, $newline);
        }
    }

    /**
     * Resolves the npm packages paths.
     *
     * @param string $rootPath The npm root path.
     * @param array  $excludes Paths to excludes.
     *
     * @return array The npm packages paths.
     */
    private function resolvePackagesPaths($rootPath, array $excludes)
    {
        $finder = Finder::create()
            ->in($rootPath)
            ->exclude(array(
                'node_modules',
                'vendor'
            ))
            ->exclude($excludes)
            ->name('package.json')
            ->sortByName();


        return iterator_to_array($finder);
    }

    /**
     * Gets the npm executable path.
     *
     * @param string $npmPath  The npm path.
     * @param string $rootPath The root path.
     *
     * @return string The npm executable path.
     *
     * @throws \InvalidArgumentException If npm executable path doesn't exists.
     */
    private function getNpmExecutablePath($npmPath, $rootPath)
    {
        if ($npmPath === null) {
            return 'npm';
        }

        $isAbsolutePath = substr($npmPath, 0, 1) === DIRECTORY_SEPARATOR;
        $executablePath = $isAbsolutePath ? realpath($npmPath) : realpath($rootPath.DIRECTORY_SEPARATOR.$npmPath);

        if ($executablePath === false) {
            $errorMessage = sprintf('%s Not Found', $npmPath);

            if (!$isAbsolutePath) {
                $errorMessage .= sprintf(' (Root path : %s)', $rootPath);
            }

            throw new \InvalidArgumentException(sprintf('<error>%s</error>', $errorMessage));
        }

        return $executablePath;
    }
}
