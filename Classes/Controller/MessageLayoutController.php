<?php

namespace Fab\Messenger\Controller;

use Fab\Messenger\Domain\Repository\MessageLayoutRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MessageLayoutController extends AbstractMessengerController
{
    protected array $allowedColumns = ['uid', 'content', 'qualifier', 'hidden'];

    public function __construct()
    {
        parent::__construct();
        $this->repository = GeneralUtility::makeInstance(MessageLayoutRepository::class);
        #$this->setRepository(GeneralUtility::makeInstance(MessageLayoutRepository::class));
        $this->setDomainModel('messagelayout');
        $this->setController('MessageLayout');
        $this->setAction('index');
        $this->setModuleName('tx_messenger_messenger_messengertxmessengerm3');
        $this->setTable('tx_messenger_domain_model_messagelayout');
        $this->setDemandFields(['content', 'qualifier']);
        $this->setShowNewButton(true);
        $this->setDefaultSelectedColumns(['uid', 'content', 'qualifier', 'hidden']);
    }
}
