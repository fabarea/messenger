<?php

namespace Fab\Messenger\Controller;

use Fab\Messenger\Domain\Repository\MessengerRepositoryInterface;
use Fab\Messenger\Domain\Repository\SentMessageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SentMessageModuleController extends AbstractMessengerController
{
    protected array $allowedColumns = [
        'uid',
        'crdate',
        'tstamp',
        'sender',
        'subject',
        'mailing_name',
        'recipient',
        'sent_time',
        'context',
        'body',
        'recipient_cc',
        'recipient_bcc',
        'redirect_email_from',
        'attachment',
        'message_template',
        'message_layout',
        'ip',
        'was_opened',
        'scheduled_distribution_time',
        'uuid',
    ];

    protected array $defaultSelectedColumns = ['sender', 'subject', 'context', 'recipient', 'sent_time'];

    protected array $demandFields = ['sender', 'recipient', 'subject', 'mailing_name', 'sent_time'];
    protected string $domainModel = 'sentmessage';
    protected string $controller = 'SendMessageModule';
    protected string $action = 'index';
    protected string $moduleName = 'tx_messenger_messenger_messengertxmessengerm1';
    protected string $table = 'tx_messenger_domain_model_sentmessage';

    protected string $dataType = 'sent-message';

    protected ?MessengerRepositoryInterface $repository;

    public function __construct()
    {
        parent::__construct();
        $this->repository = GeneralUtility::makeInstance(SentMessageRepository::class);
    }
}
