<?php

namespace Gurman\ChainCommandBundle\Tests\Unit\DependencyInjection;

use Gurman\ChainCommandBundle\DependencyInjection\ChainCommandExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use PHPUnit\Framework\TestCase;

class ChainCommandExtensionTest extends TestCase
{
    private ChainCommandExtension $extension;

    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->extension = new ChainCommandExtension();
        $this->container = new ContainerBuilder();
    }

    public function testLoadWithValidData(): void
    {
        $configs = [
            'chain_command' => [
                'chains' => [
                    [
                        'main_command' => 'app:main-command-one',
                        'members' => [
                            ['command' => 'app:sub-command-one', 'arguments' => ''],
                            ['command' => 'app:sub-command-two', 'arguments' => '-a'],
                        ]
                    ],
                ]
            ]
        ];

        $this->extension->load($configs, $this->container);
        $chains = $this->container->getDefinition('command.manager')->getMethodCalls();

        $this->assertCount(1, $chains);
        $this->assertEquals('setChains', $chains[0][0]);
        $this->assertEquals('app:main-command-one', $chains[0][1][0][0]['main_command']);
    }

    public function testLoadWithEmptyData(): void
    {
        $configs = [];

        $this->extension->load($configs, $this->container);
        $methodCalls = $this->container->getDefinition('command.manager')->getMethodCalls();

        $this->assertEmpty($methodCalls[0][1][0]);
    }
}
