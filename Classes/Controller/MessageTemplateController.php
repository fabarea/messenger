<?php

namespace Fab\Messenger\Controller;

use Fab\Messenger\Domain\Repository\MessageTemplateRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MessageTemplateController extends MainController
{
    public function __construct()
    {
        parent::__construct();
        $this->setRepository(GeneralUtility::makeInstance(MessageTemplateRepository::class));
        $this->setDomainModel('messagetemplate');
        $this->setController('MessageTemplate');
        $this->setAction('index');
        $this->setModuleName('tx_messenger_messenger_messengertxmessengerm2');
        $this->setTable('tx_messenger_domain_model_messagetemplate');
        $this->setShowNewButton(true);
        $this->setDemandFields(['type', 'subject', 'message_layout', 'qualifier']);
        $this->setDefaultSelectedColumns(['uid', 'subject', 'body']);
        $this->setAllowedColumns([
            'uid',
            'type',
            'hidden',
            'qualifier',
            'subject',
            'source_file',
            'source_page',
            'template_engine',
            'body',
            'message_layout',
        ]);
    }
}
