<?php

namespace Fab\Messenger\Controller\Ajax;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;

class ColumnSelectorController
{
    /**
     * Handle column selection updates via AJAX
     */
    public function updateColumnsAction(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $postData = $request->getParsedBody();
            $module = $postData['module'] ?? '';
            $tableName = $postData['tableName'] ?? '';
            $selectedColumns = $postData['selectedColumns'] ?? [];

            if (empty($module)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Module parameter is required'
                ], 400);
            }

            if (!is_array($selectedColumns)) {
                $selectedColumns = [];
            }

            $this->saveColumnSelection($module, $tableName, $selectedColumns);

            return new JsonResponse([
                'success' => true,
                'message' => 'Column selection updated successfully',
                'selectedColumns' => $selectedColumns
            ]);

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save column selection to backend user settings
     */
    private function saveColumnSelection(string $module, string $tableName, array $selectedColumns): void
    {
        $backendUser = $GLOBALS['BE_USER'];

        if (!$backendUser) {
            throw new \RuntimeException('No backend user found', 1619156934);
        }

        $settingsKey = 'messenger.columnSelector.' . md5($module . '.' . $tableName);

        $backendUser->pushModuleData($settingsKey, [
            'selectedColumns' => $selectedColumns,
            'lastUpdated' => time()
        ]);
    }

    /**
     * Get saved column selection from URL parameters or localStorage fallback
     */
    public static function getSavedColumnSelection(string $module, string $tableName): array
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        if ($request) {
            $queryParams = $request->getQueryParams();
            $selectedColumnsParam = $queryParams['selectedColumns'] ?? null;

            if ($selectedColumnsParam && is_array($selectedColumnsParam)) {
                $validColumns = array_filter($selectedColumnsParam, function($column) {
                    return is_string($column) && !empty($column);
                });

                $uniqueColumns = array_values(array_unique($validColumns));

                if (!empty($uniqueColumns)) {
                    return $uniqueColumns;
                }
            }
        }

        $sessionKey = 'messenger_columns_' . md5($module . '_' . $tableName);
        if (isset($_SESSION[$sessionKey])) {
            $sessionData = $_SESSION[$sessionKey];
            if (is_array($sessionData) && isset($sessionData['selectedColumns'])) {
                return $sessionData['selectedColumns'];
            }
        }

        $backendUser = $GLOBALS['BE_USER'];
        if ($backendUser) {
            $settingsKey = 'messenger.columnSelector.' . md5($module . '.' . $tableName);
            $moduleData = $backendUser->getModuleData($settingsKey);

            if ($moduleData && isset($moduleData['selectedColumns'])) {
                return $moduleData['selectedColumns'];
            }
        }

        return [];
    }
}
