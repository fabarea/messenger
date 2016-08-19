<?php
namespace Fab\Messenger\Controller;

/*
 * This file is part of the Fab/Mailing project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Mailing\Service\RecipientService;
use Fab\Messenger\Domain\Model\Message;
use Fab\Messenger\Service\SenderProvider;
use Fab\Messenger\TypeConverter\BodyConverter;
use Fab\Vidi\Domain\Model\Content;
use Fab\Vidi\Persistence\MatcherObjectFactory;
use Fab\Vidi\Service\ContentService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class MessageController
 */
class BackendMessageController extends ActionController
{

    /**
     * @throws \Fab\Media\Exception\StorageNotOnlineException
     * @throws \InvalidArgumentException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
     */
    public function initializeAction()
    {

        // Configure property mapping to retrieve the file object.
        if ($this->arguments->hasArgument('body')) {

            /** @var BodyConverter $typeConverter */
            $typeConverter = $this->objectManager->get(BodyConverter::class);

            $propertyMappingConfiguration = $this->arguments->getArgument('body')->getPropertyMappingConfiguration();
            $propertyMappingConfiguration->setTypeConverter($typeConverter);
        }
    }

    /**
     * @param array $matches
     * @throws \InvalidArgumentException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \Fab\Vidi\Exception\InvalidKeyInArrayException
     */
    public function composeAction(array $matches = array())
    {
        // Instantiate the Matcher object according different rules.
        $matcher = MatcherObjectFactory::getInstance()->getMatcher($matches, 'fe_users');

        // Fetch objects via the Content Service.
        $contentService = $this->getContentService()->findBy($matcher);

        $this->view->assign('senders', SenderProvider::getInstance()->getFormattedPossibleSenders());
        $this->view->assign('numberOfRecipients', $contentService->getNumberOfObjects());
    }

    /**
     * @param string $subject
     * @param string $body
     * @param string $sender
     * @param array $matches
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @validate $subject \Fab\Messenger\Domain\Validator\NotEmptyValidator
     * @validate $body \Fab\Messenger\Domain\Validator\NotEmptyValidator
     * @throws \InvalidArgumentException
     * @throws \Fab\Vidi\Exception\InvalidKeyInArrayException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \Fab\Messenger\Exception\InvalidEmailFormatException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \RuntimeException
     */
    public function sendAction($subject, $body, $sender, array $matches = array())
    {
        // Instantiate the Matcher object according different rules.
        $matcher = MatcherObjectFactory::getInstance()->getMatcher($matches, 'fe_users');

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
                    $message = $this->objectManager->get(Message::class);

                    # Minimum required to be set
                    $message->setBody($body)
                        ->setSubject($subject)
                        ->setSender($sender)
                        ->setMailingName($mailingName)
                        ->setScheduleDistributionTime($GLOBALS['_SERVER']['REQUEST_TIME'])
                        ->parseToMarkdown(true)// (bool)$this->settings['parseToMarkdown']
                        ->assign('recipient', $recipient->toArray())
                        ->setTo($this->getTo($recipient));

                    #if ($this->settings['layout']) {
                    #    $message->setMessageLayout($this->settings['layout']);
                    #}

                    $message->enqueue();
                }
            }
        }

        $this->redirect('feedback', null, null, [
            'numberOfSentEmails' => $numberOfSentEmails,
            'numberOfRecipients' => $contentService->getNumberOfObjects(),
        ]);
    }


    /**
     * @param Content $recipient
     * @return array
     */
    protected function getTo(Content $recipient)
    {
        $email = $recipient['email'];

        $nameParts = [];
        if ($recipient['first_name']) {
            $nameParts[] = $recipient['first_name'];
        }

        if ($recipient['last_name']) {
            $nameParts[] = $recipient['last_name'];
        }

        if (count($nameParts) > 0) {
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
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @validate $subject \Fab\Messenger\Domain\Validator\NotEmptyValidator
     * @throws \InvalidArgumentException
     * @throws \Fab\Vidi\Exception\InvalidKeyInArrayException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException
     * @throws \Fab\Messenger\Exception\InvalidEmailFormatException
     * @throws \TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException
     * @throws \RuntimeException
     */
    public function sendAsTestAction($subject, $body, $sender, $recipientList)
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
                    $message = $this->objectManager->get(Message::class);

                    # Minimum required to be set
                    $message->setBody($body)
                        ->setSubject($subject)
                        ->setSender($sender)
                        ->parseToMarkdown(true)// (bool)$this->settings['parseToMarkdown']
                        // ->assign('recipient', $recipient->toArray()) could be a security risk
                        ->setTo([$recipient => $recipient]);

                    #if ($this->settings['layout']) {
                    #    $message->setMessageLayout($this->settings['layout']);
                    #}

                    $message->send();

                    if ($numberOfSentEmails >= 10) {
                        break; // we want to stop sending email as it is for demo only.
                    }
                }
            }
        }

        $this->redirect('feedback', null, null, [
            'numberOfSentEmails' => $numberOfSentEmails,
            'numberOfRecipients' => count($recipients),
        ]);
    }

    /**
     * @param int $numberOfSentEmails
     * @param int $numberOfRecipients
     */
    public function feedbackAction($numberOfSentEmails, $numberOfRecipients)
    {
        /** @var RecipientService $recipientService */
        $this->view->assign('numberOfSentEmails', (int)$numberOfSentEmails);
        $this->view->assign('numberOfRecipients', (int)$numberOfRecipients);
    }

    /**
     * @return ContentService
     * @throws \InvalidArgumentException
     */
    protected function getContentService()
    {
        return GeneralUtility::makeInstance(ContentService::class, 'fe_users');
    }
}