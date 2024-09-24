<?php

namespace Fab\Messenger\Controller\Ajax;

use Fab\Messenger\Domain\Repository\RecipientRepository;
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
        $data = [];
        $columnsToSendString = $request->getQueryParams()['tx_messenger_user_messengerm5'] ?? '';
        if (!empty($columnsToSendString)) {
            $stringUids = explode(',', $columnsToSendString['matches']['uid']);
            $columnsToSendArray = array_map('intval', $stringUids);
            $data = $this->repository->findByUids($columnsToSendArray);
        }
        // todo use standalone view
        $content = '<f:form action="updateMany"
            additionalAttributes="{role: \'form\'}"
            id="update-many-recipients"
            method="post">

        <div class="form-group">
            <div id="message-body-container" style="{f:if(condition: pageId, then: \'display: none\')}">
                <label for="recipient-csv-list">CSV list of recipients</label>
                <f:form.textarea class="form-control"
                                 name="recipientCsvList"
                                 style="min-height: 400px"
                                 id="recipient-csv-list"
                                 placeholder="johne@doe.com;John;Doe"/>
            </div>

            <div class="checkbox">
                <label>
                    <f:form.checkbox
                            name="deleteExistingRecipients"
                            checked="true"
                            value="1"/>
                    Delete existing recipients before adding new ones.</label>
            </div>
        </div>

        <button type="submit"
                class="btn btn-primary pull-right"
                id="btn-update-many-recipients"
                style="min-width: 100px">
            Update recipients
        </button>
        <br/>
        <br/>

    </f:form>';

        $content = sprintf($content, $data[0]['email']);

        return $this->getResponse($content);
    }

    protected function getResponse(string $content): ResponseInterface
    {
        $responseFactory = GeneralUtility::makeInstance(ResponseFactoryInterface::class);
        $response = $responseFactory->createResponse();
        $response->getBody()->write($content);
        return $response;
    }

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

        $recipient = $request->getQueryParams()['recipient'] ?? '';

        var_dump($recipient);
        exit();

        $data = $request->getParsedBody();
        $this->repository->updateRecipient($data['recipient']);
        return $this->getResponse('Recipient updated');
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
