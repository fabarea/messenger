<?php
namespace Fab\Messenger\Utility;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Vidi\Tca\Tca;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Standard functions available for the TYPO3 backend.
 */
class TcaFieldsUtility
{
    public static function getFields(): array
    {
        // Fetch all available fields first.
        $fields = array_keys($GLOBALS['TCA']['tx_messenger_domain_model_sentmessage']['columns']);

        // Then remove the not allowed.
        //        $fields = self::filterByBackendUser($fields);
        //        $fields = self::filterByExcludedFields($fields);

        return $fields;
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

    protected static function hasAccess(string $fieldName): bool
    {
        $hasAccess = true;
        if (
            $this->isBackendMode() &&
            Tca::table($this->tableName)->hasAccess() &&
            isset($this->tca['exclude']) &&
            $this->tca['exclude']
        ) {
            $hasAccess = $this->getBackendUser()->check(
                'non_exclude_fields',
                $this->tableName . ':' . $this->fieldName,
            );
        }
        return $hasAccess;
    }

    protected static function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
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
        foreach ($this->getExcludedFields() as $excludedField) {
            if (isset($fields[$excludedField])) {
                unset($fields[$excludedField]);
            }
        }

        return $fields;
    }
    //    protected static function getIncludedFields(): array
    //    {
    //        return empty($this->tca['included_fields']) ? [] : GeneralUtility::trimExplode(',', $this->tca['included_fields'], true);
    //    }
}
