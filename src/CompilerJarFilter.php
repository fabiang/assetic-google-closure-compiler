<?php

/*
 * This file is part of the Assetic package, an OpenSky project.
 *
 * (c) 2010-2014 OpenSky Project Inc
 * (c) 2021 Fabian Grutschus
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Assetic\Filter\GoogleClosure;

use Assetic\Filter\GoogleClosure\BaseCompilerFilter;
use Assetic\Contracts\Asset\AssetInterface;
use Assetic\Exception\FilterException;
use Assetic\Util\FilesystemUtils;
use Symfony\Component\Process\Process;

/**
 * Filter for the Google Closure Compiler JAR.
 *
 * @link https://developers.google.com/closure/compiler/
 * @author Kris Wallsmith <kris.wallsmith@gmail.com>
 */
class CompilerJarFilter extends BaseCompilerFilter
{

    public const DEFAULT_JAVA_PATH = '/usr/bin/java';

    private string $jarPath;
    private string $javaPath;
    private ?string $flagFile = null;

    public function __construct(string $jarPath, string $javaPath = self::DEFAULT_JAVA_PATH)
    {
        $this->jarPath  = $jarPath;
        $this->javaPath = $javaPath;
    }

    public function setFlagFile(string $flagFile): void
    {
        $this->flagFile = $flagFile;
    }

    public function filterDump(AssetInterface $asset): void
    {
        $is64bit = PHP_INT_SIZE === 8;
        $cleanup = [];

        $args = array_merge(
            [$this->javaPath],
            $is64bit ? ['-server', '-XX:+TieredCompilation'] : ['-client', '-d32'],
            ['-jar', $this->jarPath]
        );

        if (null !== $this->compilationLevel) {
            $args[] = '--compilation_level';
            $args[] = $this->compilationLevel;
        }

        if (null !== $this->jsExterns) {
            $cleanup[] = $externs   = FilesystemUtils::createTemporaryFile('google_closure');
            file_put_contents($externs, $this->jsExterns);
            $args[]    = '--externs';
            $args[]    = $externs;
        }

        if (null !== $this->externsUrl) {
            $cleanup[] = $externs   = FilesystemUtils::createTemporaryFile('google_closure');
            file_put_contents($externs, file_get_contents($this->externsUrl));
            $args[]    = '--externs';
            $args[]    = $externs;
        }

        if (null !== $this->excludeDefaultExterns) {
            $args[] = '--use_only_custom_externs';
        }

        if (null !== $this->formatting) {
            $args[] = '--formatting';
            $args[] = $this->formatting;
        }

        if (null !== $this->useClosureLibrary) {
            $args[] = '--manage_closure_dependencies';
        }

        if (null !== $this->warningLevel) {
            $args[] = '--warning_level';
            $args[] = $this->warningLevel;
        }

        if (null !== $this->language) {
            $args[] = '--language_in';
            $args[] = $this->language;
        }

        if (null !== $this->flagFile) {
            $args[] = '--flagfile';
            $args[] = $this->flagFile;
        }

        $args[]    = '--js';
        $input     = FilesystemUtils::createTemporaryFile('google_closure');
        $args[]    = $input;
        $cleanup[] = $input;
        file_put_contents($input, $asset->getContent());

        $pb = new Process($args);

        if (null !== $this->timeout) {
            $pb->setTimeout($this->timeout);
        }

        $pb->run();
        $code = $pb->getExitCode();
        array_map('unlink', $cleanup);

        if (0 !== $code) {
            throw FilterException::fromProcess($pb)->setInput($asset->getContent());
        }

        $asset->setContent($pb->getOutput());
    }

}
