<?php

declare(strict_types=1);

namespace Fab\Messenger\Controller\Ajax;

use Fab\Messenger\Domain\Model\Message;
use Fab\Messenger\Domain\Repository\PageRepository;
use Fab\Messenger\Domain\Repository\RecipientRepository;
use Fab\Messenger\Exception\InvalidEmailFormatException;
use Fab\Messenger\Exception\WrongPluginConfigurationException;
use Fab\Messenger\Service\SenderProvider;
use Fab\Messenger\Utility\Algorithms;
use Fab\Messenger\Utility\ConfigurationUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class EnqueueMessageAjaxController extends AbstractMessengerAjaxController
{
    protected ?RecipientRepository $repository;
    protected PageRepository $pageRepository;

    public function __construct()
    {
        $this->repository = GeneralUtility::makeInstance(RecipientRepository::class);
        $this->pageRepository = GeneralUtility::makeInstance(PageRepository::class);
    }

    public function sendTestAction(ServerRequestInterface $request): ResponseInterface
    {
        $data = $this->getRequest()->getParsedBody();
        $data['body'] = $data['body'] ?? $this->getPageId();

        $sender = $this->getSender($data);

        if (empty($data['recipientList'])) {
            throw new WrongPluginConfigurationException(
                'No recipient found. Please configure one in the extension settings.',
                1729613978,
            );
        }
        $content = $this->sendAsTestEmail($data, $sender);
        return $this->getResponse($content);
    }

    public function enqueueAction(ServerRequestInterface $request): ResponseInterface
    {
        $data = $this->getRequest()->getParsedBody();
        $data['body'] = $data['body'] ?? $this->getPageId();

        $sender = $this->getSender($data);

        $demandList = $request->getQueryParams()['tx_messenger_user_messengerm5'] ?? '';
        $uids = empty($demandList)
            ? []
            : array_map('intval', array_filter(explode(',', $demandList['matches']['uid'])));

        $searchTerm = $request->getQueryParams()['search'] ?? '';
        $content = $this->performEnqueue($uids, $data, $sender, $searchTerm);
        return $this->getResponse($content);
    }

    protected function getSender(array $data): array
    {
        $possibleSenders = GeneralUtility::makeInstance(SenderProvider::class)->getPossibleSenders();

        $sender = array_key_exists($data['sender'], $possibleSenders)
            ? $possibleSenders[$data['sender']]
            : $possibleSenders['me'];
        if (empty($sender)) {
            throw new WrongPluginConfigurationException(
                'No sender found. Please configure one in the extension settings.',
                1728405668,
            );
        }
        return $sender;
    }

    /**
     * @throws InvalidEmailFormatException
     * @throws WrongPluginConfigurationException
     */
    private function sendAsTestEmail(array $data, array $sender): string
    {
        $emails = GeneralUtility::trimExplode(',', $data['recipientList'], true);

        $emailsArray = [];
        foreach ($emails as $emailsArrayItem) {
            if (!filter_var($emailsArrayItem, FILTER_VALIDATE_EMAIL)) {
                throw new InvalidEmailFormatException('You have an set an invalid email !');
            }
            $emailsArray[$emailsArrayItem] = $emailsArrayItem;
        }
        $numberOfSentEmails = 0;
        if (!empty($emails)) {
            $numberOfSentEmails++;
            /** @var Message $message */
            $message = GeneralUtility::makeInstance(Message::class);
            $message
                ->setBody($data['body'])
                ->setSubject($data['subject'])
                ->setSender($sender)
                ->parseToMarkdown(true)
                ->setTo($emailsArray)
                ->send();
        }

        return $numberOfSentEmails !== 1
            ? $this->getLanguageService()->sL(
                'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:message.invalidEmails',
            )
            : $this->getLanguageService()->sL(
                'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:message.success',
            );
    }

    public function performEnqueue(array $uids, array $data, array $sender, string $term): string
    {
        $demand = $this->getDemand($uids, $term);
        $recipients = $this->repository->findByDemand($demand);

        $numberOfSentEmails = 0;
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
                    ->setSender($sender)
                    ->setMailingName($mailingName)
                    ->assign('recipient', $markers)
                    ->assignMultiple($markers)
                    ->setScheduleDistributionTime($GLOBALS['_SERVER']['REQUEST_TIME'])
                    ->setTo($this->getTo($recipient))
                    ->enqueue();
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

    public function getDemand(array $uids, string $searchTerm): array
    {
        $demand = [
            'likes' => [],
            'uids' => [],
        ];

        // only if we have a list of uids
        if (!empty($uids)) {
            $demand['uids'] = $uids;
        }

        // only if we have a search term
        if (strlen($searchTerm) > 0) {
            $demandedFields = GeneralUtility::trimExplode(
                ',',
                ConfigurationUtility::getInstance()->get('recipient_default_fields'),
                true,
            );

            foreach ($demandedFields as $field) {
                $demand['likes'][$field] = $searchTerm;
            }
        }
        return $demand;
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
}
