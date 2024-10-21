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
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class EnqueueMessageAjaxController
{
    protected ?RecipientRepository $repository;
    protected PageRepository $pageRepository;

    public function __construct()
    {
        $this->repository = GeneralUtility::makeInstance(RecipientRepository::class);
        $this->pageRepository = GeneralUtility::makeInstance(PageRepository::class);
    }

    public function enqueueAction(ServerRequestInterface $request): ResponseInterface
    {
        $matches = [];
        $columnsToSendString = $request->getQueryParams()['tx_messenger_user_messengerm5'] ?? '';
        if (!empty($columnsToSendString)) {
            $stringUids = explode(',', $columnsToSendString['matches']['uid']);
            $matches = array_map('intval', $stringUids);
            $searchTerm = $request->getQueryParams()['search'] ?? '';
        }
        $pageContent = $this->pageRepository->findByUid($this->getPageId());

        $data = $this->getRequest()->getParsedBody();
        if (empty($data['body'])) {
            $data['body'] = implode(PHP_EOL, $pageContent);
        }

        $sender = $this->getSender($data);
        $content = $data['test']
            ? $this->sendAsTestEmail($data, $sender)
            : $this->performEnqueue($matches, $data, $sender, $searchTerm);

        return $this->getResponse($content);
    }

    protected function getPageId(): int
    {
        $site = $this->getRequest()->getAttribute('normalizedParams');
        $httpReferer = $site->getHttpReferer();
        $parsedUrl = parse_url($httpReferer);
        $queryString = $parsedUrl['query'] ?? '';
        parse_str($queryString, $queryParams);
        $id = $queryParams['id'] ?? null;
        return (int) $id;
    }

    private function getRequest(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }

    protected function getSender(array $data): array
    {
        $possibleSenders = GeneralUtility::makeInstance(SenderProvider::class)->getPossibleSenders();
        $sender = array_key_exists($data['sender'], $possibleSenders)
            ? $possibleSenders[$data['sender']]
            : $possibleSenders['php'];
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

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    public function performEnqueue(array $matches, array $data, array $sender, string $term): string
    {
        $recipients =
            isset($matches[0]) && $matches[0] === 0
                ? $this->repository->findAll()
                : $this->repository->findByUids($matches);
        if (!empty($term)) {
            $recipients = $this->repository->findByDemand($this->getDemand($term));
        }
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

    public function getDemand($searchTerm): array
    {
        $demand = [];
        $demandFields = GeneralUtility::trimExplode(
            ',',
            ConfigurationUtility::getInstance()->get('recipient_default_fields'),
            true,
        );
        if (strlen($searchTerm) > 0) {
            foreach ($demandFields as $field) {
                $demand[$field] = $searchTerm;
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

    protected function getResponse(string $content): ResponseInterface
    {
        $responseFactory = GeneralUtility::makeInstance(ResponseFactoryInterface::class);
        $response = $responseFactory->createResponse();
        $response->getBody()->write($content);
        return $response;
    }
}
