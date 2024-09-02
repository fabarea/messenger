<?php

namespace Fab\Messenger\Controller\Ajax;

use Fab\Messenger\Domain\Repository\MessageLayoutRepository;
use Fab\Messenger\Domain\Repository\MessageTemplateRepository;
use Fab\Messenger\Domain\Repository\SentMessageRepository;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class AjaxExportDataController
{
    public function confirmAction(ServerRequestInterface $request): ResponseInterface
    {
        $data = [];

        $columnsToSendString = $request->getQueryParams();

        $matches = [];
        $possibleKeys = [
            'tx_messenger_messenger_messengertxmessengerm1',
            'tx_messenger_messenger_messengertxmessengerm2',
            'tx_messenger_messenger_messengertxmessengerm3',
        ];

        foreach ($possibleKeys as $key) {
            if (isset($columnsToSendString[$key]['matches']['uid'])) {
                $matches = $columnsToSendString[$key]['matches']['uid'];
                break;
            }
        }

        $repositoryName = $columnsToSendString['repository'] ?? '';

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

        if (!empty($matches)) {
            $stringUids = explode(',', $matches);
            $columnsToSendArray = array_map('intval', $stringUids);
            $data = $repository->findByUids($columnsToSendArray);
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

    //    public function exportAction(ServerRequestInterface $request): ResponseInterface
    //    {
    //        $columnsToSendString = $request->getQueryParams()['tx_messenger_user_messengerm1'] ?? '';
    //        if (!empty($columnsToSendString)) {
    //            $stringUids = explode(',', $columnsToSendString['matches']['uid']);
    //            $matches = array_map('intval', $stringUids);
    //        } else {
    //            $matches = [];
    //        }
    //
    //        return $this->getResponse($content);
    //    }
}
