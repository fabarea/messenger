<?php

namespace Fab\Messenger\Controller;

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

    public function __construct()
    {
        parent::__construct();
        $this->setRepository(GeneralUtility::makeInstance(SentMessageRepository::class));
        $this->setDomainModel('sentmessage');
        $this->setController('SendMessageModule');
        $this->setAction('index');
        $this->setModuleName('tx_messenger_messenger_messengertxmessengerm1');
        $this->setTable('tx_messenger_domain_model_sentmessage');
        $this->setDemandFields(['sender', 'recipient', 'subject', 'mailing_name', 'sent_time']);
        $this->setDefaultSelectedColumns(['sender', 'subject', 'context', 'recipient', 'sent_time']);
    }
}
