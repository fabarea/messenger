<?php

namespace Fab\Messenger\Utility;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Standard functions available for the TYPO3 backend.
 */
class TcaFieldsUtility
{
    protected static string $tableName = '';

    public static function getFields(string $tableName): array
    {
        self::$tableName = $tableName;
        
        // Check if table exists in TCA
        if (!isset($GLOBALS['TCA'][self::$tableName]) || !isset($GLOBALS['TCA'][self::$tableName]['columns'])) {
            // Fallback to fe_users if the configured table doesn't exist
            if ($tableName !== 'fe_users') {
                self::$tableName = 'fe_users';
                if (!isset($GLOBALS['TCA'][self::$tableName]['columns'])) {
                    return [];
                }
            } else {
                return [];
            }
        }
        
        $fields = array_keys($GLOBALS['TCA'][self::$tableName]['columns']);

        return self::filterByBackendUser($fields);
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
            self::hasTableAccess() &&
            isset($GLOBALS['TCA'][self::$tableName]['columns'][$fieldName]['exclude']) &&
            $GLOBALS['TCA'][self::$tableName]['columns'][$fieldName]['exclude']
        ) {
            $hasAccess = self::getBackendUser()->check('non_exclude_fields', self::$tableName . ':' . $fieldName);
        }
        return $hasAccess;
    }

    protected static function hasTableAccess(): bool
    {
        return self::getBackendUser()->check('tables_modify', self::$tableName);
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

    private static function getExcludedFields(): array
    {
        return empty($GLOBALS['TCA'][self::$tableName]['excluded_fields'])
            ? []
            : GeneralUtility::trimExplode(',', $GLOBALS['TCA'][self::$tableName]['excluded_fields'], true);
    }

    protected static function getIncludedFields(): array
    {
        return empty($GLOBALS['TCA'][self::$tableName]['included_fields'])
            ? []
            : GeneralUtility::trimExplode(',', $GLOBALS['TCA'][self::$tableName]['included_fields'], true);
    }
}
