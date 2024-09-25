<?php

namespace Fab\Messenger\Controller\Ajax;

use Fab\Messenger\Domain\Repository\RecipientRepository;
use JetBrains\PhpStorm\NoReturn;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class UpdateRecipientController
{
    protected ?RecipientRepository $repository;

    public function __construct()
    {
        $this->repository = GeneralUtility::makeInstance(RecipientRepository::class);
    }

    public function editAction(ServerRequestInterface $request): ResponseInterface
    {
        $content = file_get_contents(
            GeneralUtility::getFileAbsFileName('EXT:messenger/Resources/Private/Standalone/Forms/UpdateRecipient.html'),
        );
        return $this->getResponse($content);
    }

    protected function getResponse(string $content): ResponseInterface
    {
        $responseFactory = GeneralUtility::makeInstance(ResponseFactoryInterface::class);
        $response = $responseFactory->createResponse();
        $response->getBody()->write($content);
        return $response;
    }

    #[NoReturn]
    public function saveAction(ServerRequestInterface $request): ResponseInterface
    {
        $data = [];
        $request->getParsedBody(); // todo test me!
        $columnsToSendString = $request->getQueryParams()['tx_messenger_user_messengerm5'] ?? '';
        if (!empty($columnsToSendString)) {
            $stringUids = explode(',', $columnsToSendString['matches']['uid']);
            $columnsToSendArray = array_map('intval', $stringUids);
            $data = $this->repository->findByUids($columnsToSendArray);
        }
        $request = $GLOBALS['TYPO3_REQUEST'];
        $data = $request->getParsedBody();
        var_dump([
            'Data' => $request->getQueryParams(),
            'ColumnsToSendString' => $data,
        ]);

        exit();

        $recipient = $request->getQueryParams()['recipient'] ?? '';

        var_dump($recipient);
        exit();

        $data = $request->getParsedBody();
        $this->repository->updateRecipient($data['recipient']);
        return $this->getResponse('Recipient updated');
    }

    public function messageFromRecipientAction(ServerRequestInterface $request): ResponseInterface
    {
        $content = file_get_contents(
            GeneralUtility::getFileAbsFileName('EXT:messenger/Resources/Private/Standalone/Forms/SentMessage.html'),
        );
        return $this->getResponse($content);
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
