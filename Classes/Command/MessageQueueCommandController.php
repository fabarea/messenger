<?php
namespace Fab\Messenger\Command;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Messenger\Domain\Model\Message;
use Fab\Messenger\Domain\Repository\QueueRepository;
use Fab\Messenger\Domain\Repository\SentMessageRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

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
                100
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
        $io = new SymfonyStyle($input, $output);

        $itemsPerRun = $input->getOption('items-per-run');
        $pendingMessages = $this->getQueueRepository()->findPendingMessages($itemsPerRun);

        $errorCount = $numberOfSentMessages = 0;
        /** @var Message $pendingMessage */
        foreach ($pendingMessages as $pendingMessage) {
            /** @var MailMessage $message */
            $message = unserialize($pendingMessage['message_serialized'], ['allowed_classes' => true]);

//            if ($pendingMessage['redirect_email']) {
//
//                // hack! Transmit in the message subject the application context to apply possible email redirect.
//                $dirtySubject = sprintf(
//                    '%s---%s###REDIRECT###%s',
//                    $pendingMessage['context'],
//                    $pendingMessage['redirect_email'],
//                    $message->getSubject()
//                );
//                $message->setSubject($dirtySubject);
//            }

            $isSent = $message->send();
            if ($isSent) {
                $numberOfSentMessages++;
                $this->getQueueRepository()->remove($pendingMessage);
                $this->getSentMessageRepository()->add($pendingMessage);
            } else {
                $errorCount++;
                $pendingMessage['error_count'] += 1;
                $this->getQueueRepository()->update($pendingMessage);
            }
        }

        if (!$input->getOption('silent')) {
            $io->text(sprintf(
                'I Just sent %s messages', $numberOfSentMessages
            ));
        }
        if ($errorCount > 0) {
            $io->text(sprintf(
                'I encountered %s problem while processing the message queue.', $errorCount
            ));
        }
    }

    /**
     * @return object|QueueRepository
     */
    protected function getQueueRepository(): QueueRepository
    {
        return GeneralUtility::makeInstance(QueueRepository::class);
    }

    /**
     * @return object|SentMessageRepository
     */
    protected function getSentMessageRepository(): SentMessageRepository
    {
        return GeneralUtility::makeInstance(SentMessageRepository::class);
    }

    /**
     * @return object|ObjectManager
     */
    protected function getObjectManager(): ObjectManager
    {
        return GeneralUtility::makeInstance(ObjectManager::class);
    }
}
