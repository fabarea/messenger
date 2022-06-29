<?php

namespace Fab\Messenger\Controller;

/*
 * This file is part of the Fab/Mailing project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Messenger\Domain\Model\Message;
use Fab\Messenger\Service\SenderProvider;
use Fab\Messenger\TypeConverter\BodyConverter;
use Fab\Messenger\Utility\Algorithms;
use Fab\Messenger\Utility\ConfigurationUtility;
use Fab\Vidi\Domain\Model\Content;
use Fab\Vidi\Persistence\MatcherObjectFactory;
use Fab\Vidi\Service\ContentService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Annotation\Validate;

/**
 * Class BackendMessageController
 */
class BackendMessageController extends ActionController
{

    /**
     * @return void
     */
    public function initializeAction(): void
    {
        // Configure property mapping to retrieve the file object.
        if ($this->arguments->hasArgument('body')) {

            /** @var BodyConverter $typeConverter */
            $typeConverter = GeneralUtility::makeInstance(BodyConverter::class);

            $propertyMappingConfiguration = $this->arguments->getArgument('body')->getPropertyMappingConfiguration();
            $propertyMappingConfiguration->setTypeConverter($typeConverter);
        }
    }

    /**
     * @param array $matches
     * @param int $pageId
     */
    public function composeAction(array $matches = array(), $pageId = 0): void
    {
        $recipientDataType = ConfigurationUtility::getInstance()->get('recipient_data_type');

        // Instantiate the Matcher object according different rules.
        $matcher = MatcherObjectFactory::getInstance()->getMatcher($matches, $recipientDataType);

        // Fetch objects via the Content Service.
        $contentService = $this->getContentService()->findBy($matcher);

        $emailSubject = '';
        if ($pageId > 0) {
            $page = \TYPO3\CMS\Backend\Utility\BackendUtility::getRecord('pages', $pageId);
            if (is_array($page) && isset($page['title'])) {
                $emailSubject = $page['title'];
            }
        }

        $this->view->assignMultiple([
            'matches' => $matches,
            'pageId' => $pageId,
            'emailSubject' => $emailSubject,
            'senders' => SenderProvider::getInstance()->getFormattedPossibleSenders(),
            'numberOfRecipients' => $contentService->getNumberOfObjects(),
        ]);
    }

    /**
     * @param string $subject
     * @param string $body
     * @param string $sender
     * @param array $matches
     * @param bool $parseMarkdown
     * @Validate("Fab\Messenger\Domain\Validator\NotEmptyValidator", param="subject")
     */
    public function enqueueAction(string $subject, string $body, string $sender, array $matches = array(), $parseMarkdown = false): void
    {
        $recipientDataType = ConfigurationUtility::getInstance()->get('recipient_data_type');

        // Instantiate the Matcher object according different rules.
        $matcher = MatcherObjectFactory::getInstance()->getMatcher($matches, $recipientDataType);

        // Fetch objects via the Content Service.
        $contentService = $this->getContentService()->findBy($matcher);

        $numberOfSentEmails = 0;
        $possibleSenders = SenderProvider::getInstance()->getPossibleSenders();
        if (is_array($possibleSenders) && $possibleSenders[$sender]) {
            $sender = $possibleSenders[$sender];
            $mailingName = 'Mailing #' . $GLOBALS['_SERVER']['REQUEST_TIME'];

            foreach ($contentService->getObjects() as $recipient) {

                if (filter_var($recipient['email'], FILTER_VALIDATE_EMAIL)) {
                    $numberOfSentEmails++; // increment counter.

                    /** @var Message $message */
                    $message = GeneralUtility::makeInstance(Message::class);
                    $message->setUuid(Algorithms::generateUUID());

                    $markers = $recipient->toArray();
                    $markers['uuid'] = $message->getUuid();

                    $message->setBody($body)
                        ->setSubject($subject)
                        ->setSender($sender)
                        ->setMailingName($mailingName)
                        ->setScheduleDistributionTime($GLOBALS['_SERVER']['REQUEST_TIME'])
                        ->parseToMarkdown($parseMarkdown)
                        ->assign('recipient', $markers)
                        ->assignMultiple($markers)
                        ->setTo($this->getTo($recipient))
                        ->enqueue();
                }
            }
        }

        $this->redirect('feedbackQueued', null, null, [
            'numberOfSentEmails' => $numberOfSentEmails,
            'numberOfRecipients' => $contentService->getNumberOfObjects(),
        ]);
    }

    /**
     * @param Content $recipient
     * @return array
     */
    protected function getTo(Content $recipient): array
    {
        $email = $recipient['email'];

        $nameParts = [];
        if ($recipient['first_name']) {
            $nameParts[] = $recipient['first_name'];
        }

        if ($recipient['last_name']) {
            $nameParts[] = $recipient['last_name'];
        }

        if (count($nameParts) === 0) {
            $nameParts[] = $email;
        }

        $name = implode(' ', $nameParts);

        return [$email => $name];
    }

    /**
     * @param string $subject
     * @param string $body
     * @param string $sender
     * @param string $recipientList
     * @Validate("Fab\Messenger\Domain\Validator\NotEmptyValidator", param="subject")
     */
    public function sendAsTestAction(string $subject, string $body, string $sender, string $recipientList): void
    {
        $recipients = GeneralUtility::trimExplode(',', $recipientList, true);
        $numberOfSentEmails = 0;

        $possibleSenders = SenderProvider::getInstance()->getPossibleSenders();
        if (is_array($possibleSenders) && $possibleSenders[$sender]) {
            $sender = $possibleSenders[$sender];

            foreach ($recipients as $recipient) {

                if (filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                    $numberOfSentEmails++; // increment counter.

                    /** @var Message $message */
                    $message = GeneralUtility::makeInstance(Message::class);

                    # Minimum required to be set
                    $message->setBody($body)
                        ->setSubject($subject)
                        ->setSender($sender)
                        ->parseToMarkdown(true)// (bool)$this->settings['parseToMarkdown']
                        // ->assign('recipient', $recipient->toArray()) could be a security risk
                        ->setTo([$recipient => $recipient])
                        ->send();

                    if ($numberOfSentEmails >= 10) {
                        break; // we want to stop sending email as it is for demo only.
                    }
                }
            }
        }

        $this->redirect('feedbackSent', null, null, [
            'numberOfSentEmails' => $numberOfSentEmails,
            'numberOfRecipients' => count($recipients),
        ]);
    }

    /**
     * @param int $numberOfSentEmails
     * @param int $numberOfRecipients
     */
    public function feedbackSentAction(int $numberOfSentEmails, int $numberOfRecipients): void
    {
        $this->view->assign('numberOfSentEmails', $numberOfSentEmails);
        $this->view->assign('numberOfRecipients', $numberOfRecipients);
    }

    /**
     * @param int $numberOfSentEmails
     * @param int $numberOfRecipients
     */
    public function feedbackQueuedAction(int $numberOfSentEmails, int $numberOfRecipients): void
    {
        $this->view->assign('numberOfSentEmails', $numberOfSentEmails);
        $this->view->assign('numberOfRecipients', $numberOfRecipients);
    }

    /**
     * @return ContentService|object
     */
    protected function getContentService()
    {
        $recipientDataType = ConfigurationUtility::getInstance()->get('recipient_data_type');
        return GeneralUtility::makeInstance(ContentService::class, $recipientDataType);
    }
}
