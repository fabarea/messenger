<?php

declare(strict_types=1);

namespace Fab\Messenger\Controller\Ajax;

use Fab\Messenger\Domain\Model\Message;
use Fab\Messenger\Domain\Repository\MessengerRepositoryInterface;
use Fab\Messenger\Domain\Repository\QueueRepository;
use Fab\Messenger\Domain\Repository\SentMessageRepository;
use Fab\Messenger\Exception\InvalidEmailFormatException;
use Fab\Messenger\Exception\WrongPluginConfigurationException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class SendAgainConfirmationAjaxController extends AbstractMessengerAjaxController
{
    protected ?MessengerRepositoryInterface $repository;

    public function confirmAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getQueryParams()['dataType']) {
            $this->getDataType($request->getQueryParams()['dataType']);
        }

        $uids = [];
        if (!empty($request->getQueryParams()['tx_messenger_user_messenger'])) {
            $uids = array_map(
                'intval',
                array_filter(explode(',', $request->getQueryParams()['tx_messenger_user_messenger']['matches']['uid'])),
            );
        }
        $term = $request->getQueryParams()['search'] ?? '';
        $data = $this->repository->findByDemand($this->getDemand($uids, $term));

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
        $uids = [];
        if (!empty($request->getQueryParams()['tx_messenger_user_messenger'])) {
            $uids = array_map(
                'intval',
                array_filter(explode(',', $request->getQueryParams()['tx_messenger_user_messenger']['matches']['uid'])),
            );
        }
        if ($request->getQueryParams()['dataType']) {
            $this->getDataType($request->getQueryParams()['dataType']);
        }
        $term = $request->getQueryParams()['search'] ?? '';
        $sentMessages = $this->repository->findByDemand($this->getDemand($uids, $term));

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
