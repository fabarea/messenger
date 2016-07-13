<?php
namespace Fab\Messenger\Backend;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Integration with the Extension Manager
 */
class ExtensionManager
{

    /**
     * The extension key
     *
     * @var string
     */
    protected $extensionKey = 'messenger';

    /**
     * The Configuration array
     *
     * @var array
     */
    protected $configuration = array();

    /**
     * The Configuration array
     *
     * @var array
     */
    protected $modules = array(
        'messagetemplate' => 'Message Template',
        'messagelayout' => 'Message Layout',
        'mailing' => 'Mailing',
        'sentmessage' => 'Sent message',
        'queue' => 'Queue'
    );

    /**
     * Render the BE modules list
     *
     * @return string
     */
    public function renderBeModules()
    {

        $configuration = $this->getConfiguration();

        $options = '';
        $enableModules = GeneralUtility::trimExplode(',', $configuration['enabledModules']);

        foreach ($this->modules as $moduleKey => $moduleName) {
            $checked = '';

            if (in_array($moduleKey, $enableModules)) {
                $checked = 'checked="checked"';
            }
            $options .= '<label><input type="checkbox" class="fieldEnabledModules" value="' . $moduleKey . '" ' . $checked . ' /> ' . $moduleName . '</label>';
        }

        $output = <<<EOF
				<div class="typo3-tstemplate-ceditor-row" id="userTS-enabledModules">
					<script type="text/javascript">
						(function($) {
						    $(function() {

								// Handler which will concatenate selected data types.
								$('.fieldEnabledModules').change(function() {
									var selected = [];

									$('.fieldEnabledModules').each(function(){
										if ($(this).is(':checked')) {
											selected.push($(this).val());
										}
									});
									$('#fieldEnabledModuless').val(selected.join(','));
								});
						    });
						})(jQuery);
					</script>
					$options
					<input type="hidden" id="fieldEnabledModuless" name="tx_extensionmanager_tools_extensionmanagerextensionmanager[config][enabledModules][value]" value="{$configuration['enabledModules']}" />
				</div>
EOF;

        return $output;
    }

    /**
     * Constructor
     *
     * @return array
     */
    protected function getConfiguration()
    {

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');

        /** @var \TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility $configurationUtility */
        $configurationUtility = $objectManager->get('TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility');
        $rawConfiguration = $configurationUtility->getCurrentConfiguration($this->extensionKey);

        $configuration = array();
        // Fill up configuration array with relevant values.
        foreach ($rawConfiguration as $key => $data) {
            $configuration[$key] = $data['value'];
        }

        return $configuration;
    }

}
