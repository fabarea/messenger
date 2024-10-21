<?php

declare(strict_types=1);

namespace Fab\Messenger\Controller\Ajax;

use Fab\Messenger\Domain\Model\Message;
use Fab\Messenger\Domain\Repository\MessengerRepositoryInterface;
use Fab\Messenger\Domain\Repository\QueueRepository;
use Fab\Messenger\Domain\Repository\RecipientRepository;
use Fab\Messenger\Domain\Repository\SentMessageRepository;
use Fab\Messenger\Exception\InvalidEmailFormatException;
use Fab\Messenger\Exception\WrongPluginConfigurationException;
use Fab\Messenger\Utility\ConfigurationUtility;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class SendAgainConfirmationAjaxController
{
    protected ?MessengerRepositoryInterface $repository;

    public function confirmAction(ServerRequestInterface $request): ResponseInterface
    {
        $data = [];
        $matches = [];
        $moduleNumber = '';
        $messengerKeys = [
            'tx_messenger_user_messengerm1',
            'tx_messenger_user_messengerm4',
            'tx_messenger_user_messengerm5',
        ];

        $columnsToSendString = '';

        foreach ($messengerKeys as $key) {
            if (isset($request->getQueryParams()[$key])) {
                $columnsToSendString = $request->getQueryParams()[$key];
                $moduleNumber = $key;
                break;
            }
        }

        if ($request->getQueryParams()['dataType']) {
            $this->getDataType($request->getQueryParams()['dataType']);
        }
        if (!empty($columnsToSendString)) {
            $stringUids = explode(',', $columnsToSendString['matches']['uid']);
            $matches = array_map('intval', $stringUids);
        }
        $term = $request->getQueryParams()['search'] ?? '';
        if (!empty($term)) {
            $data = $this->repository->findByDemand($this->getDemand($moduleNumber, $term));
        } else {
            $data = $matches ? $this->repository->findByUids($matches) : $this->repository->findAll();
        }
        $content =
            count($data) > 1
                ? $this->getLanguageService()->sL(
                    'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:send.messages.sure?',
                )
                : $this->getLanguageService()->sL(
                    'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:send.message.sure?',
                );

        $content = sprintf($content, count($data));
        return $this->getResponse($content);
    }

    protected function getDataType($type): void
    {
        switch ($type) {
            case 'sent-message':
                $this->repository = GeneralUtility::makeInstance(SentMessageRepository::class);
                break;
            case 'message-queue':
                $this->repository = GeneralUtility::makeInstance(QueueRepository::class);
                break;
            case 'recipient-module':
                $this->repository = GeneralUtility::makeInstance(RecipientRepository::class);
                break;
        }
    }

    public function getDemand(string $moduleNumber, string $searchTerm): array
    {
        $demandFields = $this->getDemandFields($moduleNumber);
        return !empty($searchTerm) ? array_fill_keys($demandFields, $searchTerm) : [];
    }

    private function getDemandFields(string $moduleNumber): array
    {
        switch ($moduleNumber) {
            case 'tx_messenger_user_messengerm1':
                return ['sender', 'recipient', 'subject', 'mailing_name', 'sent_time'];

            case 'tx_messenger_user_messengerm4':
                return [
                    'recipient_cc',
                    'recipient',
                    'sender',
                    'subject',
                    'body',
                    'attachment',
                    'context',
                    'mailing_name',
                    'message_template',
                    'message_layout',
                ];
            case 'tx_messenger_user_messengerm5':
                return GeneralUtility::trimExplode(
                    ',',
                    ConfigurationUtility::getInstance()->get('recipient_default_fields'),
                );
            default:
                return [];
        }
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
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
     * @throws WrongPluginConfigurationException
     */
    public function sendAgainAction(ServerRequestInterface $request): ResponseInterface
    {
        $matches = [];
        $moduleNumber = '';
        $messengerKeys = [
            'tx_messenger_user_messengerm1',
            'tx_messenger_user_messengerm4',
            'tx_messenger_user_messengerm5',
        ];

        $columnsToSendString = '';
        foreach ($messengerKeys as $key) {
            if (isset($request->getQueryParams()[$key])) {
                $columnsToSendString = $request->getQueryParams()[$key];
                $moduleNumber = $key;
                break;
            }
        }
        if (!empty($columnsToSendString)) {
            $stringUids = explode(',', $columnsToSendString['matches']['uid']);
            $matches = array_map('intval', $stringUids);
        }
        if ($request->getQueryParams()['dataType']) {
            $this->getDataType($request->getQueryParams()['dataType']);
        }
        $term = $request->getQueryParams()['search'] ?? '';
        if (!empty($term)) {
            $sentMessages = $this->repository->findByDemand($this->getDemand($moduleNumber, $term));
        } else {
            $sentMessages = $matches ? $this->repository->findByUids($matches) : $this->repository->findAll();
        }
        $numberOfSentEmails = 0;
        foreach ($sentMessages as $sentMessage) {
            /** @var Message $message */
            $message = GeneralUtility::makeInstance(Message::class);
            $isSent = $message
                ->setBody($sentMessage['body'] ?? '')
                ->setSubject($sentMessage['subject'])
                ->setSender($this->normalizeEmails($sentMessage['sender']))
                ->setTo($this->normalizeEmails($sentMessage['recipient']))
                ->send();

            if ($isSent) {
                $numberOfSentEmails++;
            }
        }

        $numberOfRecipients = is_countable($sentMessages) ? count($sentMessages) : 0;
        $content = sprintf(
            '%s %s / %s. %s',
            $this->getLanguageService()->sL(
                'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:message.success',
            ),
            $numberOfSentEmails,
            $numberOfRecipients,
            $numberOfSentEmails !== $numberOfRecipients
                ? $this->getLanguageService()->sL(
                    'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:message.invalidEmails',
                )
                : '',
        );
        return $this->getResponse($content);
    }

    protected function normalizeEmails(string $listOfFormattedEmails): array
    {
        $normalizedEmails = [];
        $formattedEmails = GeneralUtility::trimExplode(',', $listOfFormattedEmails);
        foreach ($formattedEmails as $formattedEmail) {
            $formattedEmail = trim($formattedEmail);
            if (preg_match('/^(.*) <(.*)>$/isU', $formattedEmail, $matches)) {
                if (count($matches) === 3) {
                    $normalizedEmails[$matches[2]] = $matches[1];
                }
            } else {
                if (filter_var($formattedEmail, FILTER_VALIDATE_EMAIL)) {
                    $normalizedEmails[$formattedEmail] = 'Unknown Name';
                }
            }
        }
        return $normalizedEmails;
    }

    public function getPageContent(ServerRequestInterface $request): int
    {
        $normalizedParams = $request->getAttributes()['normalizedParams'];
        $parsedUrl = parse_url($normalizedParams->getHttpReferer());
        parse_str($parsedUrl['query'], $queryParams);

        return (int) $queryParams['id'];
    }
}
