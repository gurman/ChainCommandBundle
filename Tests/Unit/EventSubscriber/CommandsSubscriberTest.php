<?php

namespace Gurman\ChainCommandBundle\Tests\Unit\EventSubscriber;

use Gurman\ChainCommandBundle\EventSubscriber\CommandsSubscriber;
use Gurman\ChainCommandBundle\Manager\CommandsManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Application;

class CommandsSubscriberTest extends TestCase
{
    private LoggerInterface|MockObject $logger;

    private CommandsManager|MockObject $commandsManager;

    private CommandsSubscriber $commandsSubscriber;

    private Command|MockObject $command;

    private Application|MockObject $application;

    public function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->commandsManager = $this->createMock(CommandsManager::class);
        $this->application = $this->createMock(Application::class);
        $this->application ->expects($this->any())
            ->method('get')
            ->willReturn($this->application);
        $this->command = $this->createMock(Command::class);
        $this->command->expects($this->any())
            ->method('getApplication')
            ->willReturn($this->application);

        $this->commandsSubscriber = new CommandsSubscriber(
            $this->logger,
            $this->commandsManager
        );
    }

    public function testBeforeCommandSuccess(): void {
        $this->commandsManager->expects($this->once())
            ->method('getMainForMember')
            ->with('app:main-command-one')
            ->willReturn(null);
        $this->commandsManager->expects($this->once())
            ->method('getMembersForMain')
            ->with('app:main-command-one')
            ->willReturn([
                ['command' => 'app:sub-command-one', 'arguments' => ''],
                ['command' => 'app:sub-command-two', 'arguments' => '-a'],
            ]);

        $this->command->expects($this->once())
            ->method('getName')
            ->willReturn('app:main-command-one');
        $event = new ConsoleCommandEvent(
            $this->command,
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );

        $this->commandsSubscriber->beforeCommand($event);
    }

    public function testBeforeCommandError(): void {
        $this->commandsManager->expects($this->once())
            ->method('getMainForMember')
            ->with('app:sub-command-one')
            ->willReturn('app:main-command-one');
        $this->commandsManager->expects($this->never())
            ->method('getMembersForMain');

        $this->command->expects($this->once())
            ->method('getName')
            ->willReturn('app:sub-command-one');
        $event = new ConsoleCommandEvent(
            $this->command,
            $this->createMock(InputInterface::class),
            $this->createMock(OutputInterface::class)
        );

        $this->commandsSubscriber->beforeCommand($event);
    }
}
