<?php

namespace Fab\Messenger\Utility;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Tca\Tca;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Standard functions available for the TYPO3 backend.
 */
class TcaFieldsUtility
{
    public static function getFields($displayFields = []): array
    {
        // Fetch all available fields first.
        $arrayFiels = [];
        $fields = array_keys($GLOBALS['TCA']['tx_messenger_domain_model_sentmessage']['columns']);
        // each column must be array with 'label' ,'name','selected','desabled' and 'pseudo' keys
        $fields = self::filterByBackendUser($fields);
        foreach ($fields as $field) {
            $arrayFiels[$field] = [
                'label' => $GLOBALS['TCA']['tx_messenger_domain_model_sentmessage']['columns'][$field]['label'],
                'name' => $field,
                'selected' => in_array($field, $displayFields, true),
            ];
        }

        return $arrayFiels;
    }

    protected static function filterByBackendUser($fields): array
    {
        if (!self::getBackendUser()->isAdmin()) {
            foreach ($fields as $fieldName => $field) {
                if (!self::hasAccess($fieldName)) {
                    unset($fields[$fieldName]);
                }
            }
        }
        return $fields;
    }

    protected static function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    protected static function hasAccess(string $fieldName): bool
    {
        $hasAccess = true;
        if (
            self::isBackendMode() &&
            Tca::table('tx_messenger_domain_model_sentmessage')->hasAccess() &&
            isset($GLOBALS['TCA']['tx_messenger_domain_model_sentmessage']['columns'][$fieldName]['exclude']) &&
            $GLOBALS['TCA']['tx_messenger_domain_model_sentmessage']['columns'][$fieldName]['exclude']
        ) {
            $hasAccess = self::getBackendUser()->check(
                'non_exclude_fields',
                'tx_messenger_domain_model_sentmessage' . ':' . $fieldName,
            );
        }
        return $hasAccess;
    }

    private static function isBackendMode(): bool
    {
        return TYPO3_MODE === 'BE';
    }

    /**
     * Remove fields according to Grid configuration.
     *
     * @param $fields
     * @return array
     */
    protected static function filterByExcludedFields($fields): array
    {
        // Unset excluded fields.
        foreach (self::getExcludedFields() as $excludedField) {
            if (isset($fields[$excludedField])) {
                unset($fields[$excludedField]);
            }
        }

        return $fields;
    }

    private static function getExcludedFields()
    {
        return empty($GLOBALS['TCA']['tx_messenger_domain_model_sentmessage']['excluded_fields'])
            ? []
            : GeneralUtility::trimExplode(
                ',',
                $GLOBALS['TCA']['tx_messenger_domain_model_sentmessage']['excluded_fields'],
                true,
            );
    }

    protected static function getIncludedFields(): array
    {
        return empty($GLOBALS['TCA']['tx_messenger_domain_model_sentmessage']['included_fields'])
            ? []
            : GeneralUtility::trimExplode(
                ',',
                $GLOBALS['TCA']['tx_messenger_domain_model_sentmessage']['included_fields'],
                true,
            );
    }
}
