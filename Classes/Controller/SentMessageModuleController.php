<?php

namespace Fab\Messenger\Controller;

use Fab\Messenger\Domain\Repository\MessengerRepositoryInterface;
use Fab\Messenger\Domain\Repository\SentMessageRepository;
use Fab\Messenger\Service\DataExportService;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Imaging\IconFactory;

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
    protected string $moduleName = 'MessengerTxMessengerM1';
    protected string $table = 'tx_messenger_domain_model_sentmessage';

    protected string $dataType = 'sent-message';

    protected ?MessengerRepositoryInterface $repository;

    public function __construct(
        ModuleTemplateFactory $moduleTemplateFactory,
        IconFactory $iconFactory,
        DataExportService $dataExportService,
        SentMessageRepository $repository
    ) {
        parent::__construct($moduleTemplateFactory, $iconFactory, $dataExportService);
        $this->repository = $repository;
    }
}
