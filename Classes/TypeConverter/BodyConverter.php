<?php
namespace Fab\Messenger\TypeConverter;

/*
 * This file is part of the Fab/Media project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use Fab\Messenger\ContentRenderer\BackendRenderer;
use Fab\Messenger\PagePath\PagePath;
use Psr\Http\Message\RequestFactoryInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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

    /**
     * @var RequestFactoryInterface
     */
    protected $requestFactory;

    public function __construct(RequestFactoryInterface $requestFactory) {
        $this->requestFactory = $requestFactory;
    }

    /**
     * Actually convert from $source to $targetType
     *
     * @param string $source
     * @param string $targetType
     * @param PropertyMappingConfigurationInterface $configuration
     * @api
     */
    public function convertFrom($source, $targetType, array $convertedChildProperties = [], PropertyMappingConfigurationInterface $configuration = null): string
    {

        $body = $source;
        if (is_numeric($source)) {
            //$parameters = []; todo
            $baseUrl = PagePath::getSiteBaseUrl($source);

            // Send TYPO3 cookies as this may affect path generation
            $additionalOptions = [
                'headers' => [
                    'Cookie' => 'fe_typo_user=' . $_COOKIE['fe_typo_user'],
                ],
                'cookies' => true,
            ];
            $url = $baseUrl . 'index.php?id=' . $source;
            $response = $this->requestFactory->request($url, 'GET', $additionalOptions);
            if ($response->getStatusCode() === 200) {
                $content = $response->getBody()->getContents();
                $body = preg_match("/<body[^>]*>(.*?)<\/body>/is", $content, $matches);

                if (is_array($matches) && $matches[0]) {
                    $body = $matches[0];
                }
            }
        }

        return $body;
    }

    /**
     * @return BackendRenderer
     */
    protected function getContentRenderer(): BackendRenderer
    {
        /** @var BackendRenderer $contentRenderer */
        return GeneralUtility::makeInstance(BackendRenderer::class);
    }
}
