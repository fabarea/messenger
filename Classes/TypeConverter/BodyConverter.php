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

            // Send TYPO3 cookies as this may affect path generation
            $jar = CookieJar::fromArray(
                [
                    'fe_typo_user' => $_COOKIE['fe_typo_user'],
                ],
                $_SERVER['HTTP_HOST'],
            );
            $url = $baseUrl . 'typo3/module/web/layout?id=' . $source;
            $response = $this->requestFactory->request($url, 'GET', ['cookies' => $jar]);
            if ($response->getStatusCode() === 200) {
                $content = $response->getBody()->getContents();
                $body = preg_match('/<body[^>]*>(.*?)<\/body>/is', $content, $matches);

                if (is_array($matches) && $matches[0]) {
                    $body = $matches[0];
                }
            }
        }

        return $body;
    }
}
