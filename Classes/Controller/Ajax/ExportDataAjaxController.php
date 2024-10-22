<?php

namespace Fab\Messenger\Controller\Ajax;

use Fab\Messenger\Domain\Repository\MessageLayoutRepository;
use Fab\Messenger\Domain\Repository\MessageTemplateRepository;
use Fab\Messenger\Domain\Repository\MessengerRepositoryInterface;
use Fab\Messenger\Domain\Repository\QueueRepository;
use Fab\Messenger\Domain\Repository\RecipientRepository;
use Fab\Messenger\Domain\Repository\SentMessageRepository;
use Fab\Messenger\Service\DataExportService;
use Fab\Messenger\Utility\ConfigurationUtility;
use Fab\Messenger\Utility\TcaFieldsUtility;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class ExportDataAjaxController extends AbstractMessengerAjaxController
{
    protected ?DataExportService $dataExportService = null;

    protected ?ServerRequestInterface $request = null;

    protected ?MessengerRepositoryInterface $repository;

    protected string $dataType = '';

    protected string $tableName = '';

    protected string $date = '';

    public function __construct()
    {
        $this->date = date('Y-m-d');
    }

    public function confirmAction(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        $matches = [];
        if (isset($this->request->getQueryParams()['tx_messenger_user_messenger']['matches']['uid'])) {
            $matches = $this->request->getQueryParams()['tx_messenger_user_messenger']['matches']['uid'];
        }
        $this->dataType = $this->request->getQueryParams()['dataType'] ?? '';
        $this->getDataType($this->dataType);
        $term = $this->request->getQueryParams()['search'] ?? '';
        if (!empty($term)) {
            $data = $this->repository->findByDemand(
                $this->getDemand($this->request->getQueryParams()['module'], $term),
            );
        } else {
            $data = $matches
                ? $this->repository->findByUids(array_map('intval', explode(',', $matches)))
                : $this->repository->findAll();
        }
        $content =
            count($data) > 1
                ? $this->getLanguageService()->sL(
                    'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:exports.sure?',
                )
                : $this->getLanguageService()->sL(
                    'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:export.sure?',
                );

        $content = sprintf($content, count($data));
        return $this->getResponse($content);
    }

    protected function getDataType($type): void
    {
        switch ($type) {
            case 'message-template':
                $this->repository = GeneralUtility::makeInstance(MessageTemplateRepository::class);
                $this->tableName = 'tx_messenger_domain_model_messagetemplate';
                break;
            case 'message-layout':
                $this->repository = GeneralUtility::makeInstance(MessageLayoutRepository::class);
                $this->tableName = 'tx_messenger_domain_model_messagelayout';
                break;
            case 'sent-message':
                $this->repository = GeneralUtility::makeInstance(SentMessageRepository::class);
                $this->tableName = 'tx_messenger_domain_model_sentmessage';
                break;
            case 'message-queue':
                $this->repository = GeneralUtility::makeInstance(QueueRepository::class);
                $this->tableName = 'tx_messenger_domain_model_queue';
                break;
            case 'recipient-module':
                $this->repository = GeneralUtility::makeInstance(RecipientRepository::class);
                $this->tableName = ConfigurationUtility::getInstance()->get('recipient_data_type');
                break;
        }
    }

    public function getDemand(string $moduleSignature, string $searchTerm): array
    {
        // todo improve demand
        $demandFields = $this->getDemandedFields($moduleSignature);
        return !empty($searchTerm) ? array_fill_keys($demandFields, $searchTerm) : [];
    }

    private function getDemandedFields(string $moduleSignature): array
    {
        switch ($moduleSignature) {
            case 'tx_messenger_messenger_messengertxmessengerm1':
                return ['sender', 'recipient', 'subject', 'mailing_name', 'sent_time'];
            case 'tx_messenger_messenger_messengertxmessengerm2':
                return ['type', 'subject', 'message_layout', 'qualifier'];
            case 'tx_messenger_messenger_messengertxmessengerm3':
                return ['content', 'qualifier'];
            case 'tx_messenger_messenger_messengertxmessengerm4':
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
            case 'tx_messenger_messenger_messengertxmessengerm5':
                return GeneralUtility::trimExplode(
                    ',',
                    ConfigurationUtility::getInstance()->get('recipient_default_fields'),
                );
            default:
                return [];
        }
    }

    public function exportAction(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        $this->dataType = $this->request->getQueryParams()['dataType'] ?? '';
        $matches = [];
        if (isset($this->request->getQueryParams()['tx_messenger_user_messenger']['matches']['uid'])) {
            $matches = $this->request->getQueryParams()['tx_messenger_user_messenger']['matches']['uid'];
        }
        $this->getDataType($this->dataType);
        $term = $this->request->getQueryParams()['search'] ?? '';

        if (!empty($term)) {
            $data = $this->repository->findByDemand(
                $this->getDemand($this->request->getQueryParams()['module'], $term),
            );
        } else {
            $data = $matches
                ? $this->repository->findByUids(array_map('intval', explode(',', $matches)))
                : $this->repository->findAll();
        }

        $uids = array_map(static function ($item) {
            return $item['uid'];
        }, $data);

        if ($this->request->getQueryParams()['format'] && $uids) {
            $columns = TcaFieldsUtility::getFields($this->tableName);
            $this->dataExportService = GeneralUtility::makeInstance(DataExportService::class);
            $this->dataExportService->setRepository($this->repository);
            $this->performExport($uids, $this->request->getQueryParams()['format'], $columns);
        }

        return $this->getResponse('Error');
    }

    protected function performExport(array $uids, string $format, array $columns): void
    {
        switch ($format) {
            case 'csv':
                $this->dataExportService->exportCsv(
                    $uids,
                    'export-' . $this->dataType . '-' . $this->date . '.csv',
                    ',',
                    '"',
                    '\\',
                    $columns,
                );
                break;
            case 'xml':
                $this->dataExportService->exportXml(
                    $uids,
                    'export-' . $this->dataType . '-' . $this->date . '.xml',
                    $columns,
                );
                break;
            default:
                $this->getResponse('Error');
        }
    }

    protected function getFile(
        string $content,
        string $filename,
        string $contentType = 'application/octet-stream',
    ): ResponseInterface {
        $responseFactory = GeneralUtility::makeInstance(ResponseFactoryInterface::class);
        $streamFactory = GeneralUtility::makeInstance(StreamFactoryInterface::class);
        $stream = $streamFactory->createStream($content);
        return $responseFactory
            ->createResponse(200)
            ->withHeader('Content-Type', $contentType)
            ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->withBody($stream);
    }
}
