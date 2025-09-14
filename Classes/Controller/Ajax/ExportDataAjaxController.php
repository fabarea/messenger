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
        $demandList = $this->request->getQueryParams()['tx_messenger_user_messenger'] ?? [];
        $uids = empty($demandList)
            ? []
            : array_map('intval', array_filter(explode(',', $demandList['matches']['uid'])));
        $this->dataType = $this->request->getQueryParams()['dataType'] ?? '';
        $this->initializeRepository($this->dataType);
        $term = $this->request->getQueryParams()['search'] ?? '';

        $data = $this->repository->findByDemand($this->getDemand($uids, $term));
        $content =
            count($data) > 1
                ? $this->getLanguageService()->sL(
                    'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:exports.sure?',
                )
                : $this->getLanguageService()->sL(
                    'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:export.sure?',
                );

        $content = $content = $content ? sprintf($content, count($data)) : 'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:data.notFound';
        return $this->getResponse($content);
    }

    public function exportAction(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        $this->dataType = $this->request->getQueryParams()['dataType'] ?? '';
        $demandList = $this->request->getQueryParams()['tx_messenger_user_messenger'] ?? [];
        $uids = empty($demandList)
            ? []
            : array_map('intval', array_filter(explode(',', $demandList['matches']['uid'])));
        $this->initializeRepository($this->dataType);
        $term = $this->request->getQueryParams()['search'] ?? '';

        $data = $this->repository->findByDemand($this->getDemand($uids, $term));

        $dataUids = array_map(static function ($item) {
            return $item['uid'];
        }, $data);

        if ($this->request->getQueryParams()['format'] && $dataUids) {
            $columns = TcaFieldsUtility::getFields($this->tableName);
            
            // Debug: Log the table name and columns for troubleshooting
            if (empty($columns)) {
                $errorMessage = 'No columns found for table: ' . $this->tableName;
                if ($this->dataType === 'recipient-module') {
                    $errorMessage .= ' (recipient_data_type: ' . ConfigurationUtility::getInstance()->get('recipient_data_type') . ')';
                }
                return $this->getResponse('Error: ' . $errorMessage);
            }
            
            $this->dataExportService = GeneralUtility::makeInstance(DataExportService::class);
            $this->dataExportService->setRepository($this->repository);
            $this->performExport($dataUids, $this->request->getQueryParams()['format'], $columns);
        } else {
            return $this->getResponse('Error: No data to export or format not specified');
        }
    }

    protected function initializeRepository(string $type): void
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
