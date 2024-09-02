<?php

namespace Fab\Messenger\Controller;

use Fab\Messenger\Domain\Repository\MessageLayoutRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MessageLayoutController extends MainController
{
    public function __construct()
    {
        parent::__construct();
        $this->setRepository(GeneralUtility::makeInstance(MessageLayoutRepository::class));
        $this->setDomainModel('messagelayout');
        $this->setController('MessageLayout');
        $this->setAction('index');
        $this->setModuleName('tx_messenger_messenger_messengertxmessengerm3');
        $this->setTable('tx_messenger_domain_model_messagelayout');
        $this->setDemandFields(['content', 'qualifier']);
        $this->setShowNewButton(true);
        $this->setDefaultSelectedColumns(['uid', 'content', 'qualifier', 'hidden']);
        $this->setAllowedColumns(['uid', 'content', 'qualifier', 'hidden']);
    }
}
