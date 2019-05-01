<?php

namespace Fab\Messenger\Controller;

/*
 * This file is part of the Fab/Mailing project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Messenger\Domain\Model\Message;
use Fab\Messenger\Domain\Repository\SentMessageRepository;
use Fab\Messenger\Queue\QueueManager;
use Fab\Vidi\Persistence\MatcherObjectFactory;
use Fab\Vidi\Service\ContentService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Lang\LanguageService;

/**
 * Class MessageSentController
 */
class MessageSentController extends ActionController
{

    /**
     * @var string
     */
    protected $tableName = 'tx_messenger_domain_model_sentmessage';

    /**
     * @param array $matches
     * @return string
     */
    public function confirmAction(array $matches = array()): string
    {
        // Instantiate the Matcher object according different rules.
        $matcher = MatcherObjectFactory::getInstance()->getMatcher($matches, $this->tableName);

        // Fetch objects via the Content Service.
        $numberOfRecipients = $this->getContentService()->findBy($matcher)->getNumberOfObjects();

        $label = $numberOfRecipients > 1
            ? $this->getLanguageService()->sL('LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:send.messages.sure?')
            : $this->getLanguageService()->sL('LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:send.message.sure?');

        return sprintf($label, $numberOfRecipients);
    }

    /**
     * @param array $matches
     * @return string
     */
    public function sendAgainAction(array $matches = array()): string
    {
        // Instantiate the Matcher object according different rules.
        $matcher = MatcherObjectFactory::getInstance()->getMatcher($matches, $this->tableName);

        // Fetch objects via the Content Service.
        $contentObjects = $this->getContentService()->findBy($matcher)->getObjects();

        $numberOfSentEmails = 0;
        $numberOfRecipients = count($contentObjects);

        foreach ($contentObjects as $contentObject) {

            /** @var Message $message */
            $message = $this->objectManager->get(Message::class);

            $isSent = $message->setBody($contentObject['body'])
                ->setSubject($contentObject['subject'])
                ->setSender($this->normalizeEmails($contentObject['sender']))
                ->setTo($this->normalizeEmails($contentObject['recipient']))
                ->send();

            if ($isSent) {
                $numberOfSentEmails++;
            }
        }

        return sprintf(
            '%s %s / %s. %s',
            $this->getLanguageService()->sL('LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:message.success'),
            $numberOfSentEmails,
            $numberOfRecipients,
            $numberOfSentEmails !== $numberOfRecipients
                ? $this->getLanguageService()->sL('LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:message.invalidEmails')
                : ''
        );
    }

    /**
     * @param string $listOfFormattedEmails
     * @return array
     */
    protected function normalizeEmails(string $listOfFormattedEmails): array
    {
        $normalizedEmails = [];
        $formattedEmails = GeneralUtility::trimExplode(',', $listOfFormattedEmails);
        foreach ($formattedEmails as $formattedEmail) {
            preg_match('/(.*) <(.*)>/isU', $formattedEmail, $matches);
            if (count($matches) === 3) {
                $normalizedEmails[$matches[2]] = $matches[1];

            }
        }
        return $normalizedEmails;
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    /**
     * @return object|SentMessageRepository
     */
    protected function getSentMessageRepository(): SentMessageRepository
    {
        return GeneralUtility::makeInstance(SentMessageRepository::class);
    }

    /**
     * @return ContentService|object
     */
    protected function getContentService()
    {
        return GeneralUtility::makeInstance(ContentService::class, $this->tableName);
    }

}
