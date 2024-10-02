<?php

declare(strict_types=1);

namespace Fab\Messenger\Controller\Ajax;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use Fab\Messenger\Domain\Model\Message;
use Fab\Messenger\Domain\Repository\RecipientRepository;
use Fab\Messenger\Exception\InvalidEmailFormatException;
use Fab\Messenger\Exception\WrongPluginConfigurationException;
use Fab\Messenger\Service\SenderProvider;
use Fab\Messenger\Utility\Algorithms;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class SendMessageController
{
    protected ?RecipientRepository $repository;

    public function __construct()
    {
        $this->repository = GeneralUtility::makeInstance(RecipientRepository::class);
    }

    public function messageFromRecipientAction(): ResponseInterface
    {
        $senders = GeneralUtility::makeInstance(SenderProvider::class)->getFormattedPossibleSenders();

        $content = file_get_contents(
            GeneralUtility::getFileAbsFileName('EXT:messenger/Resources/Private/Standalone/Forms/SentMessage.html'),
        );
        $sendersList = '';
        foreach ($senders as $sender) {
            $sendersList .=
                '<option value="' . htmlspecialchars($sender) . '">' . htmlspecialchars($sender) . '</option>';
        }
        $content = str_replace('<!-- SENDERS_PLACEHOLDER -->', $sendersList, $content);
        return $this->getResponse($content);
    }

    protected function getResponse(string $content): ResponseInterface
    {
        $responseFactory = GeneralUtility::makeInstance(ResponseFactoryInterface::class);
        $response = $responseFactory->createResponse();
        $response->getBody()->write($content);
        return $response;
    }

    /**
     * @throws InvalidEmailFormatException
     * @throws Exception
     * @throws DBALException
     * @throws WrongPluginConfigurationException
     */
    public function enqueueAction(ServerRequestInterface $request): ResponseInterface
    {
        $matches = [];
        $columnsToSendString = $request->getQueryParams()['tx_messenger_user_messengerm5'] ?? '';
        if (!empty($columnsToSendString)) {
            $stringUids = explode(',', $columnsToSendString['matches']['uid']);
            $matches = array_map('intval', $stringUids);
        }
        $data = $this->getRequest()->getParsedBody();
        $content = '';
        if ($data['sender'] && empty($data['recipientList'])) {
            $content = $this->getQueueAction($matches, $data);
        } elseif ($data['recipientList']) {
            $content = $this->sendAsTestAction($matches, $data);
        }

        return $this->getResponse($content);
    }

    private function getRequest(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }

    /**
     * @throws DBALException
     * @throws Exception
     * @throws InvalidEmailFormatException
     */
    public function getQueueAction(array $matches, array $data): string
    {
        $possibleSenders = SenderProvider::getInstance()->getPossibleSenders();
        $recipients = $this->repository->findByUids($matches);
        $numberOfSentEmails = 0;
        if ($data['sender'] && empty($data['recipientList'])) {
            $mailingName = 'Mailing #' . $GLOBALS['_SERVER']['REQUEST_TIME'];
            foreach ($recipients as $recipient) {
                if (filter_var($recipient['email'], FILTER_VALIDATE_EMAIL)) {
                    $numberOfSentEmails++;
                    /** @var Message $message */
                    $message = GeneralUtility::makeInstance(Message::class);
                    $message->setUuid(Algorithms::generateUUID());
                    $markers = $recipient;
                    $markers['uuid'] = $message->getUuid();
                    $message
                        ->setBody($data['body'])
                        ->setSubject($data['subject'])
                        ->setSender($this->getTo($recipient))
                        ->setMailingName($mailingName)
                        ->setScheduleDistributionTime($GLOBALS['_SERVER']['REQUEST_TIME'])
                        ->assign('recipient', $markers)
                        ->assignMultiple($markers)
                        ->setTo($this->getTo($recipient))
                        ->enqueue();
                }
            }
        }

        return sprintf(
            '%s %s / %s. %s',
            $this->getLanguageService()->sL(
                'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:message.success',
            ),
            $numberOfSentEmails,
            count($recipients),
            $numberOfSentEmails !== count($recipients)
                ? $this->getLanguageService()->sL(
                    'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:message.invalidEmails',
                )
                : '',
        );
    }

    protected function getTo(array $recipient): array
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

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    /**
     * @throws DBALException
     * @throws Exception
     * @throws InvalidEmailFormatException
     * @throws WrongPluginConfigurationException
     */
    public function sendAsTestAction(array $matches, array $data): string
    {
        $emails = GeneralUtility::trimExplode(',', $data['recipientList'], true);
        $emailsArray = [];
        foreach ($emails as $emailsArrayItem) {
            $emailsArray[$emailsArrayItem] = $emailsArrayItem;
        }
        $numberOfSentEmails = 0;
        $recipients = $this->repository->findByUids($matches);
        if ($data['recipientList']) {
            foreach ($recipients as $recipient) {
                if (filter_var($recipient['email'], FILTER_VALIDATE_EMAIL)) {
                    $numberOfSentEmails++;
                    /** @var Message $message */
                    $message = GeneralUtility::makeInstance(Message::class);
                    $message->setUuid(Algorithms::generateUUID());
                    $markers = $recipient;
                    $markers['uuid'] = $message->getUuid();
                    $message
                        ->setBody($data['body'])
                        ->setSubject($data['subject'])
                        ->setSender($this->getTo($recipient))
                        ->parseToMarkdown(true)
                        ->setTo($emailsArray)
                        ->send();
                    if ($numberOfSentEmails >= 10) {
                        break; // we want to stop sending email as it is for demo only.
                    }
                }
            }
        }

        return sprintf(
            '%s %s / %s. %s',
            $this->getLanguageService()->sL(
                'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:message.success',
            ),
            $numberOfSentEmails,
            count($recipients),
            $numberOfSentEmails !== count($recipients)
                ? $this->getLanguageService()->sL(
                    'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:message.invalidEmails',
                )
                : '',
        );
    }
}
