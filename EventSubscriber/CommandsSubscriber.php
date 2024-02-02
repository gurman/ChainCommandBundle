<?php

namespace Gurman\ChainCommandBundle\EventSubscriber;

use Gurman\ChainCommandBundle\Manager\CommandsManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

class CommandsSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private CommandsManager $commandsManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::COMMAND => 'beforeCommand',
        ];
    }

    public function beforeCommand(ConsoleCommandEvent $event): void
    {
        $command = $event->getCommand();
        if (!$command) {
            return;
        }

        $commandName = $command->getName();
        $mainCommandName = $this->commandsManager->getMainForMember($commandName);
        if ($mainCommandName !== null) {
            $event->getOutput()->writeln(sprintf(
                'Error: %s command is a member of %s command chain and cannot be executed on its own.',
                $commandName,
                $mainCommandName,
            ));
            $event->disableCommand();
            return;
        }

        $members = $this->commandsManager->getMembersForMain($commandName);
        if ($members !== null) {
            $this->logger->info(sprintf(
                '%s is a master command of a command chain that has registered member commands',
                $commandName,
            ));

            foreach ($members as $memberCommand) {
                $memberCommandName = $memberCommand['command'];
                $this->logger->info(sprintf(
                    '%s registered as a member of %s command chain',
                    $memberCommandName,
                    $commandName
                ));
            }

            $this->logger->info(sprintf(
                'Executing %s command itself first:',
                $commandName,
            ));


            $eventOutput = $event->getOutput();
            $application = $command?->getApplication();
            if (null === $application) {
                throw new \Exception('Application not found for console command');
            }

            $this->runCommand($application, $eventOutput, $commandName);

            $this->logger->info(sprintf(
                'Executing %s chain members:',
                $commandName,
            ));

            foreach ($members as $memberCommand) {
                $this->runCommand($application, $eventOutput, $memberCommand['command'], $memberCommand['arguments']);
            }

            $this->logger->info(sprintf(
                'Execution of %s chain completed.',
                $commandName,
            ));

            $command->setCode(function () {
                return Command::SUCCESS;
            });
        }
    }

    private function runCommand($application, $eventOutput, string $commandName, string $arguments = '')
    {
        $input = new StringInput($arguments);
        $output = new BufferedOutput();

        $application
            ->get($commandName)
            ->run($input, $output);

        $outputMessage = $output->fetch();
        $eventOutput->write($outputMessage);
        $this->logger->info($outputMessage);
    }
}
