<?php
namespace Fab\Messenger\Command;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Messenger\Domain\Repository\SentMessageRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class LogCommandController
 */
class LogCommandController extends Command
{

    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure(): void
    {
        $this->setDescription('Sent messages older than 100 days will be removed.')
            ->addOption(
                'older-than-days',
                '',
                InputOption::VALUE_OPTIONAL,
                'Remove messages older than x days',
                100
            );
    }

    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output);

        $olderThanDays = $input->getOption('older-than-days');
        $oldSentMessages = $this->getSentMessageRepository()->findOlderThanDays($olderThanDays);

        $numberOfOldSentMessage = count($oldSentMessages);

        if ($numberOfOldSentMessage > 0) {
            $this->getSentMessageRepository()->removeOlderThanDays($olderThanDays);
            $io->text(
                sprintf(
                    'I removed %s sent messages older than %s days from the log.',
                    $numberOfOldSentMessage,
                    $olderThanDays
                )
            );
        }
    }

    /**
     * @return object|SentMessageRepository
     */
    protected function getSentMessageRepository(): SentMessageRepository
    {
        return GeneralUtility::makeInstance(SentMessageRepository::class);
    }

}
