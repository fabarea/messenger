<?php

namespace Fab\Messenger\PagePath;

/*
 * This file is part of the Fab/Messenger project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use GuzzleHttp\Cookie\CookieJar;
use Psr\Http\Message\RequestFactoryInterface;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;

/**
 * This class create frontend page address from the page id value and parameters.
 *
 * @author Dmitry Dulepov <dmitry@typo3.org>
 */
class PagePath
{
    protected RequestFactoryInterface $requestFactory;

    public function __construct(RequestFactoryInterface $requestFactory)
    {
        $this->requestFactory = $requestFactory;
    }

    /**
     * Creates URL to page using page id and parameters
     *
     * @param int $pageId
     * @param mixed $parameters
     */
    public static function getUrl(int $pageId, mixed $parameters): string
    {
        if (is_array($parameters)) {
            $parameters = GeneralUtility::implodeArrayForUrl('', $parameters);
        }
        $data = ['id' => (int) $pageId];
        if ($parameters !== '' && $parameters[0] === '&') {
            $data['parameters'] = $parameters;
        }
        $siteUrl = self::getSiteBaseUrl($pageId);

        if ($siteUrl) {
            $url = $siteUrl . 'index.php?eID=messenger&data=' . base64_encode(serialize($data));

            $requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
            // Send TYPO3 cookies as this may affect path generation
            $cookies = [];
            if (isset($_COOKIE['fe_typo_user'])) {
                $cookies['fe_typo_user'] = $_COOKIE['fe_typo_user'];
            }
            $jar = CookieJar::fromArray($cookies, $_SERVER['HTTP_HOST']);
            $response = $requestFactory->request($url, 'GET', ['cookies' => $jar]);
            $result = $response->getBody()->getContents();

            $urlParts = parse_url($result);
            if (!is_array($urlParts)) {
                // filter_var is too strict (for example, underscore characters make it fail). So we use parse_url here for a quick check.
                $result = '';
            } elseif ($result) {
                // See if we need to prepend domain part
                if (!isset($urlParts['host']) || $urlParts['host'] === '') {
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
    public static function getSiteBaseUrl(int $pageId): string
    {
        $environmentBaseUrl = null;
        $baseUrl = null;
        // CLI must define its own environment variable.
        if (Environment::isCli()) {
            // TODO remove this condition

            die(
            'You should never see that message. Please report to https://github.com/fabarea/messenger if that is the case'
            );

            // @deprecated.
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
            $siteRootPage = [];
            $domainName = '';
            foreach (BackendUtility::BEgetRootLine($pageId) as $page) {
                if ((int) $page['is_siteroot'] === 1) {
                    $siteRootPage = $page;
                }
            }
            #if (!empty($siteRootPage)) {
            #    $domain = self::guessFistDomain($siteRootPage['uid']);
            #    if (!empty($domain)) {
            #        $domainName = $domain['domainName'];
            #    }
            #}
            $domainName = null;
            $baseUrl = $domainName
                ? self::getScheme($siteRootPage['uid']) . '://' . $domainName . '/'
                : GeneralUtility::getIndpEnv('TYPO3_SITE_URL');
        }

        return $baseUrl;
    }

    /**
     * @param int $pageId
     * @return string
     */
    protected static function getScheme(int $pageId): string
    {
        $pageRecord = BackendUtility::getRecord('pages', $pageId);
        return is_array($pageRecord) &&
        isset($pageRecord['url_scheme']) &&
        $pageRecord['url_scheme'] === HttpUtility::SCHEME_HTTPS
            ? 'https'
            : 'http';
    }

    /**
     * @param int $pageId
     * @return array
     */
    #protected static function guessFistDomain(int $pageId): array
    #{
    #    /** @var QueryBuilder $query */
    #    $queryBuilder = self::getQueryBuilder('sys_domain');
    #    $queryBuilder->select('*')
    #        ->from('sys_domain')
    #        ->andWhere(
    #            'pid = ' . $pageId
    #        )
    #        ->addOrderBy('sorting', 'ASC');
    #
    #    $record = $queryBuilder
    #        ->execute()
    #        ->fetch();
    #    return is_array($record)
    #        ? $record
    #        : [];
    #}

    /**
     * @param string $tableName
     * @return QueryBuilder
     */
    protected static function getQueryBuilder(string $tableName): QueryBuilder
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        return $connectionPool->getQueryBuilderForTable($tableName);
    }
}
