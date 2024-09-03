<?php

namespace Fab\Messenger\Controller\Ajax;

use enshrined\svgSanitize\data\AllowedAttributes;
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

        $repositoryName = $this->request->getQueryParams()['repository'] ?? '';
        $repository = '';
        switch ($repositoryName) {
            case 'MessageTemplateRepository':
                $repository = GeneralUtility::makeInstance(MessageTemplateRepository::class);
                break;
            case 'MessageLayoutRepository':
                $repository = GeneralUtility::makeInstance(MessageLayoutRepository::class);
                break;

            case 'SentMessageRepository':
                $repository = GeneralUtility::makeInstance(SentMessageRepository::class);
                break;
        }
        $uids = [];
        if (!empty($matches)) {
            $stringUids = explode(',', $matches);
            $uids = array_map('intval', $stringUids);
            $data = $repository->findByUids($uids);
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

    //    public function validateAction(ServerRequestInterface $request): ResponseInterface
    //    {
    //        $this->request = $request;
    //        $matches = [];
    //        $possibleKeys = [
    //            'tx_messenger_messenger_messengertxmessengerm1',
    //            'tx_messenger_messenger_messengertxmessengerm2',
    //            'tx_messenger_messenger_messengertxmessengerm3',
    //        ];
    //        foreach ($possibleKeys as $key) {
    //            if (isset($this->request->getQueryParams()[$key]['matches']['uid'])) {
    //                $matches = $this->request->getQueryParams()[$key]['matches']['uid'];
    //                break;
    //            }
    //        }
    //        $dataType = $this->request->getQueryParams()['repository'] ?? ''; // todo rename dataType "MessageTemplate", "message-template"
    //        $repository = '';
    //        $tableName = '';
    //        switch ($dataType) {
    //            case 'MessageTemplateRepository':
    //                $repository = GeneralUtility::makeInstance(MessageTemplateRepository::class);
    //                $tableName = 'tx_messenger_domain_model_messagetemplate';
    //                break;
    //            case 'MessageLayoutRepository':
    //                $repository = GeneralUtility::makeInstance(MessageLayoutRepository::class);
    //                $tableName = 'tx_messenger_domain_model_messagelayout';
    //                break;
    //            case 'SentMessageRepository':
    //                $repository = GeneralUtility::makeInstance(SentMessageRepository::class);
    //                $tableName = 'tx_messenger_domain_model_sentmessage';
    //                break;
    //        }
    //        $uids = [];
    //        if (!empty($matches)) {
    //            $stringUids = explode(',', $matches);
    //            $uids = array_map('intval', $stringUids);
    //        }
    //        if ($this->request->getQueryParams()['format'] && $uids) {
    //            $columns = TcaFieldsUtility::getFields($tableName);
    //            $this->dataExportService = GeneralUtility::makeInstance(DataExportService::class);
    //            $this->dataExportService->setRepository($repository);
    //            $this->exportAction($uids, $this->request->getQueryParams()['format'], $columns);
    //        }
    //        $filename = 'export.csv';
    //        $content = 'Error';
    //        return $this->getFile($content, $filename);
    //    }

    public function exportAction(array $uids, string $format, array $columns): void
    {
        // todo compute the filename
        switch ($format) {
            case 'csv':
                $this->dataExportService->exportCsv($uids, 'export.csv', ',', '"', '\\', $columns);
                break;
            case 'xls':
                $this->dataExportService->exportXls($uids, 'export.xls', $columns);
                break;
            case 'xml':
                $this->dataExportService->exportXml($uids, 'export.xml', $columns);
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
