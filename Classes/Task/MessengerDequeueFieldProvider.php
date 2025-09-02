<?php

namespace Fab\Messenger\Task;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * Additional BE fields for ip address anonymization task.
 * @internal This class is a specific scheduler task implementation is not considered part of the Public TYPO3 API.
 */
class MessengerDequeueFieldProvider extends AbstractAdditionalFieldProvider
{
    public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $schedulerModule): array
    {
        $additionalFields = [];
        $additionalFields['task_messenger_itemsPerRun'] = $this->getNumberOfDaysAdditionalField(
            $taskInfo,
            $task,
            $schedulerModule,
        );
        return $additionalFields;
    }

    protected function getNumberOfDaysAdditionalField(
        array &$taskInfo,
        $task,
        SchedulerModuleController $schedulerModule,
    ): array {
        $fieldId = 'scheduler_messenger_itemsPerRun';
        if (empty($taskInfo[$fieldId])) {
            $taskInfo[$fieldId] = $task->itemsPerRun ?? 300;
        }
        $fieldName = 'tx_scheduler[' . $fieldId . ']';
        $fieldHtml =
            '<input class="form-control" type="text" ' .
            'name="' .
            $fieldName .
            '" ' .
            'id="' .
            $fieldId .
            '" ' .
            'value="' .
            (int) $taskInfo[$fieldId] .
            '" ' .
            'size="4">';
        return [
            'code' => $fieldHtml,
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:label.messenger.itemsPerRun',
            'cshKey' => '_MOD_system_txschedulerM1',
            'cshLabel' => $fieldId,
        ];
    }

    public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $schedulerModule): bool
    {
        return $this->validateNumberOfDaysAdditionalField($submittedData, $schedulerModule);
    }

    public function validateNumberOfDaysAdditionalField(
        array &$submittedData,
        SchedulerModuleController $schedulerModule,
    ): bool {
        $validData = false;
        if (!isset($submittedData['scheduler_messenger_itemsPerRun'])) {
            $validData = true;
        } elseif ((int) $submittedData['scheduler_messenger_itemsPerRun'] >= 0) {
            $validData = true;
        } else {
            AbstractAdditionalFieldProvider::addMessage(
                $this->getLanguageService()->sL(
                    'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:message.invalidNumberOfItemsPerRun',
                ),
                ContextualFeedbackSeverity::ERROR,
            );
        }
        return $validData;
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    public function saveAdditionalFields(array $submittedData, AbstractTask $task): void
    {
        $task->itemsPerRun = (int) $submittedData['scheduler_messenger_itemsPerRun'];
    }
}
