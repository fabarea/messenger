<?php

declare(strict_types=1);

namespace Fab\Messenger\Controller\Ajax;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class SendAgainConfirmationAjaxController
{
    public function confirmAction(ServerRequestInterface $request): ResponseInterface
    {
        // Instantiate the Matcher object according different rules.
        //        $matcher = MatcherObjectFactory::getInstance()->getMatcher($matches, $this->tableName);
        //
        //        // Fetch objects via the Content Service.
        //        $numberOfRecipients = $this->getContentService()->findBy($matcher)->getNumberOfObjects();

        #$sentMessageRepository = GeneralUtility::makeInstance(MessageTemplateRepository::class);

        $numberOfRecipients = 1;
        $label =
            $numberOfRecipients > 1
                ? $this->getLanguageService()->sL(
                    'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:send.messages.sure?',
                )
                : $this->getLanguageService()->sL(
                    'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:send.message.sure?',
                );

        $responseFactory = GeneralUtility::makeInstance(ResponseFactoryInterface::class);
        $response = $responseFactory->createResponse(); // ->withHeader('Content-Type', 'application/json; charset=utf-8');
        $response->getBody()->write('<div>asd123f</div>');
        return $response;
    }
}
