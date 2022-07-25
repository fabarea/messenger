<?php

namespace Fab\Messenger\Task;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Scheduler\AbstractAdditionalFieldProvider;
use TYPO3\CMS\Scheduler\Controller\SchedulerModuleController;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

/**
 * Additional BE fields for ip address anonymization task.
 * @internal This class is a specific scheduler task implementation is not considered part of the Public TYPO3 API.
 */
class MessengerDequeueFieldProvider extends AbstractAdditionalFieldProvider
{
    /**
     * Add additional fields
     *
     * @param array $taskInfo Reference to the array containing the info used in the add/edit form
     * @param AbstractTask|null $task When editing, reference to the current task. NULL when adding.
     * @param SchedulerModuleController $schedulerModule Reference to the calling object (Scheduler's BE module)
     * @return array Array containing all the information pertaining to the additional fields
     */
    public function getAdditionalFields(array &$taskInfo, $task, SchedulerModuleController $schedulerModule)
    {
        $additionalFields = [];
        $additionalFields['task_messenger_itemsPerRun'] = $this->getNumberOfDaysAdditionalField($taskInfo, $task, $schedulerModule);
        return $additionalFields;
    }

    /**
     * Add an input field to get the number of days.
     *
     * @param array $taskInfo Reference to the array containing the info used in the add/edit form
     * @param AbstractTask|null $task When editing, reference to the current task. NULL when adding.
     * @param SchedulerModuleController $schedulerModule Reference to the calling object (Scheduler's BE module)
     * @return array Array containing all the information pertaining to the additional fields
     */
    protected function getNumberOfDaysAdditionalField(array &$taskInfo, $task, SchedulerModuleController $schedulerModule)
    {
        $fieldId = 'scheduler_messenger_itemsPerRun';
        if (empty($taskInfo[$fieldId])) {
            $taskInfo[$fieldId] = $task->itemsPerRun ?? 300;
        }
        $fieldName = 'tx_scheduler[' . $fieldId . ']';
        $fieldHtml = '<input class="form-control" type="text" ' . 'name="' . $fieldName . '" ' . 'id="' . $fieldId . '" ' . 'value="' . (int)$taskInfo[$fieldId] . '" ' . 'size="4">';
        $fieldConfiguration = [
            'code' => $fieldHtml,
            'label' => 'LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:label.messenger.itemsPerRun',
            'cshKey' => '_MOD_system_txschedulerM1',
            'cshLabel' => $fieldId
        ];
        return $fieldConfiguration;
    }

    /**
     * Validate additional fields
     *
     * @param array $submittedData Reference to the array containing the data submitted by the user
     * @param SchedulerModuleController $schedulerModule Reference to the calling object (Scheduler's BE module)
     * @return bool True if validation was ok (or selected class is not relevant), false otherwise
     */
    public function validateAdditionalFields(array &$submittedData, SchedulerModuleController $schedulerModule)
    {
        $validData = $this->validateNumberOfDaysAdditionalField($submittedData, $schedulerModule);
        return $validData;
    }


    /**
     * Checks if given number of days is a positive integer
     *
     * @param array $submittedData Reference to the array containing the data submitted by the user
     * @param SchedulerModuleController $schedulerModule Reference to the calling object (Scheduler's BE module)
     * @return bool True if validation was ok (or selected class is not relevant), false otherwise
     */
    public function validateNumberOfDaysAdditionalField(array &$submittedData, SchedulerModuleController $schedulerModule)
    {
        $validData = false;
        if (!isset($submittedData['scheduler_messenger_itemsPerRun'])) {
            $validData = true;
        } elseif ((int)$submittedData['scheduler_messenger_itemsPerRun'] >= 0) {
            $validData = true;
        } else {
            // Issue error message
            $this->addMessage(
                $this->getLanguageService()->sL('LLL:EXT:messenger/Resources/Private/Language/locallang.xlf:message.invalidNumberOfItemsPerRun'),
                FlashMessage::ERROR
            );
        }
        return $validData;
    }

    /**
     * Save additional field in task
     *
     * @param array $submittedData Contains data submitted by the user
     * @param AbstractTask $task Reference to the current task object
     */
    public function saveAdditionalFields(array $submittedData, AbstractTask $task)
    {
        $task->itemsPerRun = (int)$submittedData['scheduler_messenger_itemsPerRun'];
    }

    /**
     * Returns an instance of LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
