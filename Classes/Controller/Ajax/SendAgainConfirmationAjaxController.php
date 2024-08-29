<?php

declare(strict_types=1);

namespace Fab\Messenger\Controller\Ajax;

use Fab\Messenger\Domain\Model\Message;
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
    public function confirmAction(ServerRequestInterface $request): ResponseInterface
    {
        $data = [];
        $columnsToSendString = $request->getQueryParams()['tx_messenger_user_messengerm1'] ?? '';
        if (!empty($columnsToSendString)) {
            $stringUids = explode(',', $columnsToSendString['matches']['uid']);
            $columnsToSendArray = array_map('intval', $stringUids);
            $data = $this->getSentMessageRepository()->findByUids($columnsToSendArray);
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

    /**
     * @throws InvalidEmailFormatException
     * @throws WrongPluginConfigurationException
     */
    public function sendAgainAction(ServerRequestInterface $request): ResponseInterface
    {
        $columnsToSendString = $request->getQueryParams()['tx_messenger_user_messengerm1'] ?? '';
        if (!empty($columnsToSendString)) {
            $stringUids = explode(',', $columnsToSendString['matches']['uid']);
            $matches = array_map('intval', $stringUids);
        } else {
            $matches = [];
        }
        $sentMessages = $this->getSentMessageRepository()->findByUids($matches);
        $numberOfSentEmails = 0;
        foreach ($sentMessages as $sentMessage) {
            /** @var Message $message */
            $message = GeneralUtility::makeInstance(Message::class);
            $isSent = $message
                ->setBody($sentMessage['body'])
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
    protected function getResponse(string $content): ResponseInterface
    {
        $responseFactory = GeneralUtility::makeInstance(ResponseFactoryInterface::class);
        $response = $responseFactory->createResponse();
        $response->getBody()->write($content);
        return $response;
    }

    protected function getSentMessageRepository(): SentMessageRepository
    {
        return GeneralUtility::makeInstance(SentMessageRepository::class);
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
