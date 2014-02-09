<?php

namespace Uecode\Bundle\QPushBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class QueueBuildCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('uecode:qpush:destroy')
            ->setDescription('Destroys the configured Queues and cleans Cache')
            ->addArgument(
                'name',
                InputArgument::OPTIONAL,
                'Name of a specific queue to destroy',
                null
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $registry = $this->getContainer()->get('uecode_qpush');
        $dialog = $this->getHelperSet()->get('dialog');

        $name = $input->getArgument('name');

        if (null !== $name) {
            $response = $dialog->askConfirmation(
                $output,
                sprintf(
                    '<question>This will remove the <info>%s</info> queue, even if it has messages! Are you sure?</question>',
                    $name
                ),
                false
            );

            if (!$response) {
                return;
            }

            return $this->destroyQueue($registry, $name);
        }

        $response = $dialog->askConfirmation(
            $output,
            '<question>This will remove <info>ALL</info> queues, even if they have messages.  Are you sure?</question>',
            false
        );

        if (!$response) {
                return;
        }

        foreach ($registry->all() as $queue) {
            $this->destroyQueue($registry, $queue->getName());
        }

    }

    private function destroyQueue($registry, $name)
    {
        if (!$registry->has($name)) {
            return $output->writeln(
                sprintf("The [%s] queue you have specified does not exists!", $name)
            );
        }

        $registry->get($name)->destroy();
        $this->output->writeln(sprintf("The %s queue has been built successfully.", $name));

        return 0;
    }
}
