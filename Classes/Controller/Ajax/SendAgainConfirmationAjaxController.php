<?php

declare(strict_types=1);

namespace Fab\Messenger\Controller\Ajax;

use Fab\Messenger\Domain\Model\Message;
use Fab\Messenger\Domain\Repository\MessengerRepositoryInterface;
use Fab\Messenger\Domain\Repository\QueueRepository;
use Fab\Messenger\Domain\Repository\SentMessageRepository;
use Fab\Messenger\Exception\InvalidEmailFormatException;
use Fab\Messenger\Exception\WrongPluginConfigurationException;
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
        $matches = [];
        if ($request->getQueryParams()['dataType']) {
            $this->getDataType($request->getQueryParams()['dataType']);
        }

        if (!empty($request->getQueryParams()['tx_messenger_user_messenger'])) {
            $stringUids = explode(',', $request->getQueryParams()['tx_messenger_user_messenger']['matches']['uid']);
            if (!empty($stringUids) && $stringUids[0] !== '') {
                $matches = array_map('intval', $stringUids);
            }
        }
        $term = $request->getQueryParams()['search'] ?? '';
        if ($term != '') {
            $data = $this->repository->findByDemand($this->getDemand($this->getModuleName($request), $term));
        } else {
            $data = $matches[0] != 0 ? $this->repository->findByUids($matches) : $this->repository->findAll();
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
        }
    }

    public function getDemand(string $moduleName, string $searchTerm): array
    {
        $demandFields = $this->getDemandFields($moduleName);
        return !empty($searchTerm) ? array_fill_keys($demandFields, $searchTerm) : [];
    }

    private function getDemandFields(string $moduleName): array
    {
        switch ($moduleName) {
            case 'MessengerTxMessengerM1':
                return ['sender', 'recipient', 'subject', 'mailing_name', 'sent_time'];

            case 'MessengerTxMessengerM4':
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
            default:
                return [];
        }
    }

    protected function getModuleName(ServerRequestInterface $request): string
    {
        $pathSegments = explode(
            '/',
            trim(parse_url($request->getAttributes()['normalizedParams']->getHttpReferer())['path'], '/'),
        );
        return end($pathSegments);
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
        if (!empty($request->getQueryParams()['tx_messenger_user_messenger'])) {
            $stringUids = explode(',', $request->getQueryParams()['tx_messenger_user_messenger']['matches']['uid']);
            $matches = array_map('intval', $stringUids);
        }
        if ($request->getQueryParams()['dataType']) {
            $this->getDataType($request->getQueryParams()['dataType']);
        }
        $term = $request->getQueryParams()['search'] ?? '';
        if (!empty($term) && $term != '') {
            $sentMessages = $this->repository->findByDemand($this->getDemand($this->getModuleName($request), $term));
        } else {
            $sentMessages = $matches[0] != 0 ? $this->repository->findByUids($matches) : $this->repository->findAll();
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
}
