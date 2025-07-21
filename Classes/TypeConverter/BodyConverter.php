<?php

namespace Fab\Messenger\TypeConverter;

use Fab\Messenger\PagePath\PagePath;
use GuzzleHttp\Cookie\CookieJar;
use Psr\Http\Message\RequestFactoryInterface;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\AbstractTypeConverter;

class BodyConverter extends AbstractTypeConverter
{
    protected  $sourceTypes = ['int', 'string'];
    protected  $targetType = 'string';
    protected  $priority = 1;

    protected RequestFactoryInterface $requestFactory;

    public function __construct(RequestFactoryInterface $requestFactory)
    {
        $this->requestFactory = $requestFactory;
    }

    public function convertFrom(
        $source,
        string $targetType,
        array $convertedChildProperties = [],
        PropertyMappingConfigurationInterface $configuration = null,
    ): string {
        if (!is_numeric($source)) {
            return (string)$source;
        }
        $pageId = (int)$source;
        $baseUrl = rtrim(PagePath::getSiteBaseUrl($pageId), '/');
        $cookieJar = $this->createCookieJar();

        $headContent = $this->fetchBackendHeadContent($baseUrl, $pageId, $cookieJar);
        $customHeader = $this->buildCustomHeader($baseUrl);
        $frontendBody = $this->fetchFrontendBody($baseUrl, $pageId, $cookieJar);

        return $headContent . $customHeader . $frontendBody;
    }

    private function createCookieJar(): CookieJar
    {
        $cookie = $_COOKIE['fe_typo_user'] ?? '';
        $host = filter_var($_SERVER['HTTP_HOST'] ?? 'localhost', FILTER_SANITIZE_URL);

        return CookieJar::fromArray(['fe_typo_user' => $cookie], $host);
    }

    private function fetchBackendHeadContent(string $baseUrl, int $pageId, CookieJar $jar): string
    {
        $url = "{$baseUrl}/typo3/module/web/layout?id={$pageId}";
        $response = $this->requestFactory->request($url, 'GET', ['cookies' => $jar]);

        if ($response->getStatusCode() !== 200) {
            return '';
        }

        $content = $response->getBody()->getContents();
        if (!preg_match('/<head[^>]*>(.*?)<\/head>/is', $content, $matches)) {
            return '';
        }

        $headContent = $matches[1];
        return $this->adjustRelativePaths($headContent, $baseUrl);
    }

    private function fetchFrontendBody(string $baseUrl, int $pageId, CookieJar $jar): string
    {
        $url = "{$baseUrl}/index.php?id={$pageId}";
        $response = $this->requestFactory->request($url, 'GET', ['cookies' => $jar]);

        if ($response->getStatusCode() !== 200) {
            return '';
        }

        $content = $response->getBody()->getContents();

        if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $content, $matches)) {
            $bodyContent = $matches[1];
        } else {
            $bodyContent = $content;
        }
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $html = '<?xml encoding="UTF-8">' . $bodyContent;
        $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $xpath = new \DOMXPath($dom);
        foreach ($xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " container-fluid ")]') as $containerDiv) {
            $containerDiv->parentNode->removeChild($containerDiv);
        }
        $cleanedBody = $dom->saveHTML();
        libxml_clear_errors();
        return $cleanedBody;
    }

    private function adjustRelativePaths(string $html, string $baseUrl): string
    {
        $patterns = [
            '/(href|src)=["\']\/(typo3\/|fileadmin\/|uploads\/|t3-assets\/|typo3temp\/|assets\/)/',
            '/(url\(["\']?)\/(typo3\/|fileadmin\/|uploads\/|t3-assets\/|typo3temp\/|assets\/)/',
            '/(data-src|data-href)=["\']\/(typo3\/|fileadmin\/|uploads\/|t3-assets\/|typo3temp\/|assets\/)/',
        ];

        foreach ($patterns as $pattern) {
            $html = preg_replace_callback($pattern, function ($matches) use ($baseUrl) {
                return $matches[1] . '="' . $baseUrl . '/' . $matches[2];
            }, $html);
        }

        return $html;
    }

    private function buildCustomHeader($baseUrl): string
    {
        return <<<HTML
        <style>
            body {
                font-family: Arial, serif;
                font-size: 16px;
                line-height: 1.8em;
                color: #333333;
                padding: 0;
                margin: 0;
            }
            .container { width: 80%; margin: auto; }
            h1 { margin: 0; padding: 0; }
            #footer a { color: white; }
            #footer .container div { display: inline; }
            #header { height: 50px; line-height: 50px; background: #3176a2; color: white; text-align: right; font-size: 12px; }
            #header a { color: white; }
            #header2 { background: #cce4ee; color: white; font-weight: bold; padding: 10px 20px; height: 80px; margin-bottom: 20px; }
            #footer { background: #3176a2; color: white; padding: 10px 20px; }
            #logo img { position: relative; top: -40px; display: block; margin: auto; }
        </style>
        <div id="header2">
            <div class="container">
                <div id="logo">
                    <a href="{$baseUrl}">
                        <img src="{$baseUrl}/ichtus_logo.png" alt="ICHTUS" width="150px" height="150px">
                    </a>
                </div>
            </div>
        </div>
HTML;
    }
}
