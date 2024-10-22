<?php

namespace Fab\Messenger\Controller\Ajax;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use Fab\Messenger\Domain\Repository\RecipientRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class UpdateRecipientAjaxController extends AbstractMessengerAjaxController
{
    protected ?RecipientRepository $repository;

    public function __construct()
    {
        $this->repository = GeneralUtility::makeInstance(RecipientRepository::class);
    }

    public function editAction(): ResponseInterface
    {
        $content = file_get_contents(
            GeneralUtility::getFileAbsFileName('EXT:messenger/Resources/Private/Standalone/Forms/UpdateRecipient.html'),
        );
        return $this->getResponse($content);
    }

    /**
     * @throws DBALException
     * @throws Exception
     */
    public function saveAction(): ResponseInterface
    {
        $data = $this->getRequest()->getParsedBody();
        if ($data['deleteExistingRecipients']) {
            $this->repository->deleteAllAction();
        }
        $recipients = GeneralUtility::trimExplode("\n", trim($data['recipientCsvList']));
        $counter = count($recipients);
        $created = 0;
        foreach ($recipients as $recipientCsv) {
            $recipient = GeneralUtility::trimExplode(';', $recipientCsv);
            if (count($recipient) >= 3) {
                [$email, $firstName, $lastName] = $recipient;
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    if ($data['deleteExistingRecipients'] || !$this->repository->exists($email)) {
                        $values = [
                            'email' => $email,
                            'first_name' => $firstName,
                            'last_name' => $lastName,
                        ];
                        $this->repository->insert($values);
                    }
                }

                $created++;
            }
        }
        $content = sprintf('Created %s/%s', $created, $counter);
        return $this->getResponse($content);
    }
}
