<?php
namespace Fab\Messenger\Command;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Messenger\Queue\QueueManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Controller
 */
class MessageQueueCommandController extends Command
{

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure(): void
    {
        $this->setDescription('Send messages and remove them from the queue by batch of 100 messages.')
            ->addOption(
                'items-per-run',
                'i',
                InputOption::VALUE_OPTIONAL,
                'Items to be processed by each run',
                300
            )
            ->addOption(
                'silent',
                's',
                InputOption::VALUE_OPTIONAL,
                'If true, only the errors are displayed on the CLI',
                false
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $itemsPerRun = $input->getOption('items-per-run');
        $result = $this->getQueueManager()->dequeue($itemsPerRun);

        $io = new SymfonyStyle($input, $output);
        if (!$input->getOption('silent')) {
            $io->text(sprintf(
                'I just sent %s messages', $result['numberOfSentMessages']
            ));
        }
        if ($result['errorCount'] > 0) {
            $io->text(sprintf(
                'I encountered %s problem(s) while processing the message queue.', $result['errorCount']
            ));
        }
    }

    /**
     * @return object|QueueManager
     */
    protected function getQueueManager(): QueueManager
    {
        return GeneralUtility::makeInstance(QueueManager::class);
    }
}
