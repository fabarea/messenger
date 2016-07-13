<?php
namespace Fab\Messenger\PagePath;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;

/**
 * This class create frontend page address from the page id value and parameters.
 *
 * @author Dmitry Dulepov <dmitry@typo3.org>
 */
class PagePath
{

    /**
     * Creates URL to page using page id and parameters
     *
     * @param int $pageId
     * @param string $parameters
     * @return    string    Path to page or empty string
     */
    static public function getUrl($pageId, $parameters = '')
    {
        if (is_array($parameters)) {
            $parameters = GeneralUtility::implodeArrayForUrl('', $parameters);
        }
        $data = array(
            'id' => intval($pageId),
        );
        if ($parameters != '' && $parameters{0} == '&') {
            $data['parameters'] = $parameters;
        }
        $siteUrl = self::getSiteUrl($pageId);

        if ($siteUrl) {
            $url = $siteUrl . 'index.php?eID=messenger&data=' . base64_encode(serialize($data));

            // Send TYPO3 cookies as this may affect path generation
            $headers = array(
                'Cookie: fe_typo_user=' . $_COOKIE['fe_typo_user']
            );

            $result = GeneralUtility::getURL($url, false, $headers);

            $urlParts = parse_url($result);
            if (!is_array($urlParts)) {

                // filter_var is too strict (for example, underscore characters make it fail). So we use parse_url here for a quick check.
                $result = '';
            } elseif ($result) {

                // See if we need to prepend domain part
                if ($urlParts['host'] == '') {
                    $result = rtrim($siteUrl, '/') . '/' . ltrim($result, '/');
                }
            }

        } else {
            $result = '';
        }
        return $result;
    }

    /**
     * Obtains site URL.
     *
     * @static
     * @param int $pageId
     * @return string
     */
    static protected function getSiteUrl($pageId)
    {

        // CLI must define its own environment variable.
        if (TYPO3_cliMode === TRUE) {

            $environmentBaseUrl = getenv('TYPO3_BASE_URL');
            $baseUrl = rtrim($environmentBaseUrl, '/') . '/';
            if (!$baseUrl) {
                $message = 'ERROR in Messenger!' . chr(10);
                $message .= 'I can not send emails because of missing environment variable TYPO3_BASE_URL' . chr(10);
                $message .= 'You can set it when calling the CLI script as follows:' . chr(10) . chr(10);
                $message .= 'TYPO3_BASE_URL=http://www.domain.tld typo3/cli_dispatch.phpsh scheduler' . chr(10);
                die($message);
            }
        } else {
            $domain = BackendUtility::firstDomainRecord(BackendUtility::BEgetRootLine($pageId));
            $pageRecord = BackendUtility::getRecord('pages', $pageId);
            $scheme = is_array($pageRecord) && isset($pageRecord['url_scheme']) && $pageRecord['url_scheme'] == HttpUtility::SCHEME_HTTPS ? 'https' : 'http';
            $baseUrl = $domain ? $scheme . '://' . $domain . '/' : GeneralUtility::getIndpEnv('TYPO3_SITE_URL');
        }

        return $baseUrl;
    }

}
