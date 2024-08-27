<?php

declare(strict_types=1);

namespace Fab\Messenger\Controller\Ajax;

use Fab\Messenger\Domain\Model\Message;
use Fab\Messenger\Domain\Repository\SentMessageRepository;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

final class SendAgainConfirmationAjaxController
{
    public function confirmAction(ServerRequestInterface $request): ResponseInterface
    {


        $columnsToSendString = $request->getQueryParams()['tx_messenger_user_messengerm1'] ?? '';
        if (!empty($columnsToSendString)) {
            $stringUids = explode(',', $columnsToSendString['matches']['uid']);
            $columnsToSendArray = array_map('intval', $stringUids);
            $sentMessagesRepository = GeneralUtility::makeInstance(SentMessageRepository::class);
            $data = $sentMessagesRepository->findByUids($columnsToSendArray);
        } else {
            $data = [];
        }
        $label =
            count($data) > 1
                ? $this->getLanguageService()->sL(
                    'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:send.messages.sure?',
                )
                : $this->getLanguageService()->sL(
                    'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:send.message.sure?',
                );

        $label = sprintf($label, count($data));
        $responseFactory = GeneralUtility::makeInstance(ResponseFactoryInterface::class);
        $response = $responseFactory->createResponse();
        $response->getBody()->write($label);
        return $response;
    }


    public function sendAgainAction(ServerRequestInterface $request): string
    {
        $columnsToSendString = $request->getQueryParams()['tx_messenger_user_messengerm1'] ?? '';
        if (!empty($columnsToSendString)) {
            $stringUids = explode(',', $columnsToSendString['matches']['uid']);
            $matches = array_map('intval', $stringUids);
        } else {
            $matches = [];
        }
        $sentMessagesRepository = GeneralUtility::makeInstance(SentMessageRepository::class);
        $contentObjects = $sentMessagesRepository->findByUids($matches);
        $numberOfSentEmails = 0;
        $numberOfRecipients = is_countable($contentObjects) ? count($contentObjects) : 0;

        foreach ($contentObjects as $contentObject) {
            /** @var Message $message */
            $message = GeneralUtility::makeInstance(Message::class);
            $isSent = $message
                ->setBody($contentObject['body'])
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
    }
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
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

}
