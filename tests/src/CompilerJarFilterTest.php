<?php

declare(strict_types=1);

namespace Assetic\Filter\GoogleClosure;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Assetic\Contracts\Asset\AssetInterface;

/**
 * @coversDefaultClass Assetic\Filter\GoogleClosure\CompilerJarFilter
 */
final class CompilerJarFilterTest extends TestCase
{

    use ProphecyTrait;

    private CompilerJarFilter $compiler;

    protected function setUp(): void
    {
        $this->compiler = new CompilerJarFilter(
            'vendor/packagelist/closurecompiler-bin/bin/compiler.jar',
            CompilerJarFilter::DEFAULT_JAVA_PATH
        );
    }

    /**
     * @coversNothing
     */
    public function testFilterDump(): void
    {
        $output     = [];
        $returnCode = 0;
        exec('which java', $output, $returnCode);
        if (0 !== $returnCode) {
            $this->markTestSkipped('Java is not installed');
        }

        $asset = $this->prophesize(AssetInterface::class);
        $asset->getContent()
            ->shouldBeCalled()
            ->willReturn('function test () { var test = 1; return test; }');

        $asset->setContent("function test(){return 1};\n")
            ->shouldBeCalled();

        $this->compiler->filterDump($asset->reveal());
    }

}
