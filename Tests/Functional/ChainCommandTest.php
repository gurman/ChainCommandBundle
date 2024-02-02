<?php

namespace Gurman\ChainCommandBundle\Tests\Functional;

use Gurman\ChainCommandBundle\Command\Test\MainOneCommand;
use Gurman\ChainCommandBundle\Command\Test\SubOneCommand;
use Gurman\ChainCommandBundle\Command\Test\SubTwoCommand;
use Gurman\ChainCommandBundle\Manager\CommandsManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class ChainCommandTest extends KernelTestCase
{
    protected Application $application;

    protected OutputInterface $bufferedOutput;

    public function setUp(): void
    {
        parent::setUp();

        /** @var KernelInterface $kernel */
        $kernel = static::createKernel();
        //$kernel = new \Gurman\ChainCommandBundle\Tests\Kernel();
        //$application = new Application(self::$kernel);
        $this->application = new Application($kernel);
        $this->application->setAutoExit(false);
        $this->bufferedOutput = new BufferedOutput();

        $this->application->add(new MainOneCommand());
        $this->application->add(new SubOneCommand());
        $this->application->add(new SubTwoCommand());

        $container = $kernel->getContainer();
        /** @var CommandsManager $commandsManager */
        $commandsManager  = $container->get('command.manager');
        $commandsManager->setChains([
            [
                'main_command' => 'app:main-command-one',
                'members' => [
                    ['command' => 'app:sub-command-one', 'arguments' => ''],
                    ['command' => 'app:sub-command-two', 'arguments' => ''],
                ]
            ],
        ]);
    }

    public function testChainCommandsCallSuccess(): void
    {
        $resultCode = $this->application->run(
            new ArrayInput(['app:main-command-one']),
            $this->bufferedOutput
        );
        self::assertSame(Command::SUCCESS, $resultCode);
        self::assertEquals("main-command-one \nsub-command-one \nsub-command-two \n", $this->bufferedOutput->fetch());
    }

    public function testChainCommandsCallFailed(): void
    {
        $errorMessage = "Error: app:sub-command-one command is a member of app:main-command-one command chain and cannot be executed on its own.\n";
        $resultCode = $this->application->run(
            new ArrayInput(['app:sub-command-one']),
            $this->bufferedOutput
        );
        self::assertSame(113, $resultCode);
        self::assertEquals($errorMessage, $this->bufferedOutput->fetch());
    }
}
