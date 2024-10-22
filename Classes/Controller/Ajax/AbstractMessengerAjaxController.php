<?php

namespace Fab\Messenger\Controller\Ajax;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractMessengerAjaxController
{
    protected function getResponse(string $content): ResponseInterface
    {
        $responseFactory = GeneralUtility::makeInstance(ResponseFactoryInterface::class);
        $response = $responseFactory->createResponse();
        $response->getBody()->write($content);
        return $response;
    }

    protected function getModuleName(ServerRequestInterface $request): string
    {
        $pathSegments = explode(
            '/',
            trim(parse_url($request->getAttributes()['normalizedParams']->getHttpReferer())['path'], '/'),
        );
        return end($pathSegments);
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

    protected function getRequest(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }
}
