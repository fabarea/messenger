<?php

namespace Fab\Messenger\Controller\Ajax;

use Fab\Messenger\Utility\ConfigurationUtility;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractMessengerAjaxController
{
    protected function getDemand(array $uids, string $searchTerm): array
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
            $demandedFields = $this->getDemandedFields();
            foreach ($demandedFields as $field) {
                $demand['likes'][$field] = $searchTerm;
            }
        }
        return $demand;
    }

    protected function getDemandedFields(): array
    {
        $demandedFields = [];
        switch ($this->getModule()) {
            case 'MessengerTxMessengerM1':
                $demandedFields = ['sender', 'recipient', 'subject', 'mailing_name', 'sent_time'];
                break;
            case 'MessengerTxMessengerM2':
                $demandedFields = ['type', 'subject', 'message_layout', 'qualifier'];
                break;
            case 'MessengerTxMessengerM3':
                $demandedFields = ['content', 'qualifier'];
                break;
            case 'MessengerTxMessengerM4':
                $demandedFields = [
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
                break;
            case 'MessengerTxMessengerM5':
                $demandedFields = GeneralUtility::trimExplode(
                    ',',
                    ConfigurationUtility::getInstance()->get('recipient_default_fields'),
                );
                break;
        }

        return $demandedFields;
    }

    protected function getModule(): string
    {
        $pathSegments = explode(
            '/',
            trim(parse_url($this->getRequest()->getAttributes()['normalizedParams']->getHttpReferer())['path'], '/'),
        );
        return end($pathSegments);
    }

    protected function getRequest(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }

    protected function getResponse(string $content): ResponseInterface
    {
        $responseFactory = GeneralUtility::makeInstance(ResponseFactoryInterface::class);
        $response = $responseFactory->createResponse();
        $response->getBody()->write($content);
        return $response;
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

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}