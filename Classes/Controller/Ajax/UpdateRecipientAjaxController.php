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
        $request = $this->getRequest();
        $data = $request->getParsedBody();
        
        // Debug: log received data
        $debugInfo = 'Received data: ' . json_encode($data) . "\n";
        $debugInfo .= 'Content-Type: ' . ($request->getHeaderLine('Content-Type') ?? 'not set') . "\n";

        $deleteExistingRecipients = isset($data['deleteExistingRecipients']) && $data['deleteExistingRecipients'];
        $recipientCsvList = $data['recipientCsvList'] ?? '';

        if ($deleteExistingRecipients) {
            $this->repository->deleteAllAction();
        }
        $recipients = GeneralUtility::trimExplode("\n", trim($recipientCsvList));
        $counter = count($recipients);
        $created = 0;
        $updated = 0;
        foreach ($recipients as $recipientCsv) {
            $recipient = GeneralUtility::trimExplode(';', $recipientCsv);
            if (count($recipient) >= 3) {
                [$email, $firstName, $lastName] = $recipient;
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $values = [
                        'email' => $email,
                        'first_name' => $firstName,
                        'last_name' => $lastName,
                    ];
                    
                    if ($deleteExistingRecipients) {
                        // If we're deleting existing, just insert
                        $this->repository->insert($values);
                        $created++;
                    } else {
                        // Update if exists, insert if not
                        if ($this->repository->exists($email)) {
                            $this->repository->updateByEmail($email, $values);
                            $updated++;
                        } else {
                            $this->repository->insert($values);
                            $created++;
                        }
                    }
                }
            }
        }
        $content = sprintf('Created: %s, Updated: %s, Total: %s/%s - Debug: %s', $created, $updated, ($created + $updated), $counter, $debugInfo);
        return $this->getResponse($content);
    }
}
