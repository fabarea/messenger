<?php

namespace Fab\Messenger\Controller\Ajax;

use Fab\Messenger\Domain\Repository\MessageLayoutRepository;
use Fab\Messenger\Domain\Repository\MessageTemplateRepository;
use Fab\Messenger\Domain\Repository\SentMessageRepository;
use Fab\Messenger\Service\DataExportService;
use Fab\Messenger\Utility\TcaFieldsUtility;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class ExportDataAjaxController
{
    protected ?DataExportService $dataExportService = null;

    protected ?ServerRequestInterface $request = null;

    protected SentMessageRepository|MessageLayoutRepository|MessageTemplateRepository $repository;

    protected string $dataType = '';

    protected string $tableName = '';

    protected string $date = '';

    public function __construct()
    {
        $this->date = date('Y-m-d');
    }

    public function confirmAction(ServerRequestInterface $request): ResponseInterface
    {
        $data = [];
        $this->request = $request;

        $matches = [];
        $possibleKeys = [
            'tx_messenger_messenger_messengertxmessengerm1',
            'tx_messenger_messenger_messengertxmessengerm2',
            'tx_messenger_messenger_messengertxmessengerm3',
        ];
        foreach ($possibleKeys as $key) {
            if (isset($this->request->getQueryParams()[$key]['matches']['uid'])) {
                $matches = $this->request->getQueryParams()[$key]['matches']['uid'];
                break;
            }
        }

        $this->dataType = $this->request->getQueryParams()['dataType'] ?? '';

        $this->getDataType($this->dataType);

        if (!empty($matches)) {
            $stringUids = explode(',', $matches);
            $uids = array_map('intval', $stringUids);
            $data = $this->repository->findByUids($uids);
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
            case 'message-template-repository':
                $this->repository = GeneralUtility::makeInstance(MessageTemplateRepository::class);
                $this->tableName = 'tx_messenger_domain_model_messagetemplate';
                break;
            case 'message-layout-repository':
                $this->repository = GeneralUtility::makeInstance(MessageLayoutRepository::class);
                $this->tableName = 'tx_messenger_domain_model_messagelayout';
                break;
            case 'sent-message-repository':
                $this->repository = GeneralUtility::makeInstance(SentMessageRepository::class);
                $this->tableName = 'tx_messenger_domain_model_sentmessage';
                break;
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

    public function validateAction(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        $this->dataType = $this->request->getQueryParams()['dataType'] ?? '';
        $matches = [];
        $possibleKeys = [
            'tx_messenger_messenger_messengertxmessengerm1',
            'tx_messenger_messenger_messengertxmessengerm2',
            'tx_messenger_messenger_messengertxmessengerm3',
        ];
        foreach ($possibleKeys as $key) {
            if (isset($this->request->getQueryParams()[$key]['matches']['uid'])) {
                $matches = $this->request->getQueryParams()[$key]['matches']['uid'];
                break;
            }
        }
        $this->getDataType($this->dataType);

        $uids = [];
        if (!empty($matches)) {
            $stringUids = explode(',', $matches);
            $uids = array_map('intval', $stringUids);
        }
        if ($this->request->getQueryParams()['format'] && $uids) {
            $columns = TcaFieldsUtility::getFields($this->tableName);
            $this->dataExportService = GeneralUtility::makeInstance(DataExportService::class);
            $this->dataExportService->setRepository($this->repository);
            $this->exportAction($uids, $this->request->getQueryParams()['format'], $columns);
        }

        return $this->getResponse('Error');
    }

    public function exportAction(array $uids, string $format, array $columns): void
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
            case 'xls':
                $this->dataExportService->exportXls(
                    $uids,
                    'export-' . $this->dataType . '-' . $this->date . '.xls',
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
