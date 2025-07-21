<?php

namespace Fab\Messenger\TypeConverter;

/*
 * This file is part of the Fab/Media project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Messenger\PagePath\PagePath;
use GuzzleHttp\Cookie\CookieJar;
use Psr\Http\Message\RequestFactoryInterface;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\AbstractTypeConverter;

/**
 * Class BodyConverter
 */
class BodyConverter extends AbstractTypeConverter
{
    /**
     * @var array<string>
     */
    protected $sourceTypes = ['int', 'string'];

    /**
     * @var string
     */
    protected $targetType = 'string';

    /**
     * @var integer
     */
    protected $priority = 1;

    protected RequestFactoryInterface $requestFactory;

    public function __construct(RequestFactoryInterface $requestFactory)
    {
        $this->requestFactory = $requestFactory;
    }

    /**
     * Actually convert from $source to $targetType
     *
     * @param string $source
     * @param string $targetType
     * @param array $convertedChildProperties
     * @param PropertyMappingConfigurationInterface|null $configuration
     * @return string
     * @api
     */
    public function convertFrom(
        $source,
        string $targetType,
        array $convertedChildProperties = [],
        PropertyMappingConfigurationInterface $configuration = null,
    ): string {
        $body = $source;
        if (is_numeric($source)) {
            //$parameters = []; todo
            $baseUrl = PagePath::getSiteBaseUrl($source);

            $jar = CookieJar::fromArray(
                [
                    'fe_typo_user' => $_COOKIE['fe_typo_user'] ?? '',
                    'be_typo_user' => $_COOKIE['be_typo_user'] ?? '',
                    'typo3_sess' => $_COOKIE['typo3_sess'] ?? '',
                ],
                $_SERVER['HTTP_HOST'] ?? 'localhost',
            );

            $frontendUrl = $baseUrl . 'index.php?id=' . $source;
            $headers = [
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Charset' => 'utf-8',
                'Accept-Language' => 'fr,en;q=0.9',
                'User-Agent' => 'TYPO3-Messenger/1.0',
                'Cache-Control' => 'no-cache',
                'Referer' => $baseUrl,
            ];

            $frontendResponse = $this->requestFactory->request($frontendUrl, 'GET', [
                'cookies' => $jar,
                'headers' => $headers,
                'timeout' => 30
            ]);

            if ($frontendResponse->getStatusCode() === 200) {
                $frontendContent = $frontendResponse->getBody()->getContents();

                $frontendContent = quoted_printable_decode($frontendContent);

                $frontendContent = html_entity_decode($frontendContent, ENT_QUOTES | ENT_HTML5, 'UTF-8');

                $frontendContent = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $frontendContent);

                preg_match('/<body[^>]*>(.*?)<\/body>/is', $frontendContent, $bodyMatches);
                $bodyContent = $bodyMatches[1] ?? '';

                $backendUrl = $baseUrl . 'typo3/module/web/layout?id=' . $source;
                $backendResponse = $this->requestFactory->request($backendUrl, 'GET', ['cookies' => $jar]);

                $headContent = '';
                if ($backendResponse->getStatusCode() === 200) {
                    $backendContent = $backendResponse->getBody()->getContents();
                    $backendContent = quoted_printable_decode($backendContent);
                    $backendContent = html_entity_decode($backendContent, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $backendContent = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $backendContent);

                    preg_match('/<head[^>]*>(.*?)<\/head>/is', $backendContent, $headMatches);
                    $headContent = $headMatches[1] ?? '';
                }

                $baseUrl = rtrim($baseUrl, '/');

                $resourceBaseUrl = $baseUrl;

                $patterns = [
                    '/(href|src)=["\']\/(typo3\/|fileadmin\/|uploads\/|t3-assets\/|typo3temp\/|assets\/)/',
                    '/(url\(["\']?)\/(typo3\/|fileadmin\/|uploads\/|t3-assets\/|typo3temp\/|assets\/)/',
                    '/(data-src|data-href)=["\']\/(typo3\/|fileadmin\/|uploads\/|t3-assets\/|typo3temp\/|assets\/)/'
                ];

                foreach ($patterns as $pattern) {
                    $replacement = function($matches) use ($resourceBaseUrl) {
                        // Use normal base URL for resources
                        return $matches[1] . '="' . $resourceBaseUrl . '/' . $matches[2];
                    };

                    $headContent = preg_replace_callback(
                        $pattern,
                        $replacement,
                        $headContent
                    );
                    $bodyContent = preg_replace_callback(
                        $pattern,
                        $replacement,
                        $bodyContent
                    );
                }

                $body = '<div class="typo3-messenger-content">' . $headContent . $bodyContent . '</div>';
            }
        }

        return $body;
    }

}
