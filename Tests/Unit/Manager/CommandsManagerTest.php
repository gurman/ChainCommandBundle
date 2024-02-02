<?php

namespace Gurman\ChainCommandBundle\Tests\Unit\Manager;

use PHPUnit\Framework\TestCase;
use Gurman\ChainCommandBundle\Manager\CommandsManager;

class CommandsManagerTest extends TestCase
{
    private CommandsManager $commandsManager;

    protected function setUp(): void
    {
        $this->commandsManager = new CommandsManager();
        $this->commandsManager->setChains([
            [
                'main_command' => 'app:main-command-one',
                'members' => [
                    ['command' => 'app:sub-command-one', 'arguments' => ''],
                    ['command' => 'app:sub-command-two', 'arguments' => '-a'],
                ]
            ],
            [
                'main_command' => 'app:main-command-two',
                'members' => [
                    ['command' => 'app:sub-command-three', 'arguments' => ''],
                    ['command' => 'app:sub-command-four', 'arguments' => '-r -w'],
                ]
            ]
        ]);
    }

    public function testGetMembersForMain(): void
    {
        $expectedMembers = [
            ['command' => 'app:sub-command-one', 'arguments' => ''],
            ['command' => 'app:sub-command-two', 'arguments' => '-a'],
        ];
        $actualMembers = $this->commandsManager->getMembersForMain('app:main-command-one');
        $this->assertEquals($expectedMembers, $actualMembers);

        $actualMembers = $this->commandsManager->getMembersForMain('non-existent-main-command');
        $this->assertNull($actualMembers);
    }

    public function testGetMainForMember(): void
    {
        $expectedMainCommand = 'app:main-command-one';
        $actualMainCommand = $this->commandsManager->getMainForMember('app:sub-command-one');
        $this->assertEquals($expectedMainCommand, $actualMainCommand);

        $actualMainCommand = $this->commandsManager->getMainForMember('non-existent-member-command');
        $this->assertNull($actualMainCommand);
    }
}
