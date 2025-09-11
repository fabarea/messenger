<?php

declare(strict_types=1);

namespace Fab\Messenger\Controller\Ajax;

use Fab\Messenger\Domain\Repository\MessageLayoutRepository;
use Fab\Messenger\Domain\Repository\MessageTemplateRepository;
use Fab\Messenger\Domain\Repository\MessengerRepositoryInterface;
use Fab\Messenger\Domain\Repository\QueueRepository;
use Fab\Messenger\Domain\Repository\RecipientRepository;
use Fab\Messenger\Domain\Repository\SentMessageRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MassDeletionAjaxController extends AbstractMessengerAjaxController
{
    protected ?MessengerRepositoryInterface $repository;

    public function confirmAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getQueryParams()['dataType']) {
            $this->initializeRepository($request->getQueryParams()['dataType']);
        }

        $uids = [];
        if (!empty($request->getQueryParams()['tx_messenger_user_messenger'])) {
            $uids = array_map(
                'intval',
                array_filter(explode(',', $request->getQueryParams()['tx_messenger_user_messenger']['matches']['uid'])),
            );
        }
        $term = $request->getQueryParams()['search'] ?? '';
        $data = $this->repository->findByDemand($this->getDemand($uids, $term));
        $content =
            count($data) != 0
                ? $this->getLanguageService()->sL(
                    'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:delete.messages.sure?',
                )
                : null;

        $content = $content = $content ? sprintf($content, count($data)) : 'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:data.notFound';
        return $this->getResponse($content);
    }

    protected function initializeRepository(string $type): void
    {
        switch ($type) {
            case 'message-template':
                $this->repository = GeneralUtility::makeInstance(MessageTemplateRepository::class);
                break;
            case 'message-layout':
                $this->repository = GeneralUtility::makeInstance(MessageLayoutRepository::class);
                break;
            case 'sent-message':
                $this->repository = GeneralUtility::makeInstance(SentMessageRepository::class);
                break;
            case 'message-queue':
                $this->repository = GeneralUtility::makeInstance(QueueRepository::class);
                break;
            case 'recipient-module':
                $this->repository = GeneralUtility::makeInstance(RecipientRepository::class);
                break;
        }
    }

    public function deleteAction(ServerRequestInterface $request): ResponseInterface
    {
        if ($request->getQueryParams()['dataType']) {
            $this->initializeRepository($request->getQueryParams()['dataType']);
        }
        $uids = [];
        if (!empty($request->getQueryParams()['tx_messenger_user_messenger'])) {
            $uids = array_map(
                'intval',
                array_filter(explode(',', $request->getQueryParams()['tx_messenger_user_messenger']['matches']['uid'])),
            );
        }
        $term = $request->getQueryParams()['search'] ?? '';
        $data = $this->repository->findByDemand($this->getDemand($uids, $term));
        $result = $this->repository->deleteByUids(array_map(static fn(array $item) => $item['uid'], $data));
        $content = sprintf(
            '%s %s / %s',
            $this->getLanguageService()->sL(
                'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:delete.success',
            ),
            $result,
            count($data),
        );

        return $this->getResponse($content);
    }
}
