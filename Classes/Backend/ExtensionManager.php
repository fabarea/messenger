<?php
namespace Vanilla\Messenger\Backend;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Fabien Udriot <fabien.udriot@ecodev.ch>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Integration with the Extension Manager
 */
class ExtensionManager {

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
		'composer' => 'Composer',
		'messagetemplate' => 'Message Template',
		'messagelayout' => 'Message Layout',
		'mailing' => 'Mailing',
		'sentmessage' => 'Sent message',
		'queue' => 'Queue'
	);

	/**
	 * Constructor
	 *
	 * @return \Vanilla\Messenger\Backend\ExtensionManager
	 */
	public function __construct() {

		/** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
		$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');

		/** @var \TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility $configurationUtility */
		$configurationUtility = $objectManager->get('TYPO3\CMS\Extensionmanager\Utility\ConfigurationUtility');
		$configuration = $configurationUtility->getCurrentConfiguration($this->extensionKey);

		// Fill up configuration array with relevant values.
		foreach ($configuration as $key => $data) {
			$this->configuration[$key] = $data['value'];
		}
	}

	/**
	 * Render the BE modules list
	 *
	 * @return string
	 */
	public function renderBeModules() {

		$options = '';
		$enableModules = GeneralUtility::trimExplode(',', $this->configuration['enabledModules']);

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
						$(document).ready(function() {

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
					</script>
					$options
					<input type="hidden" id="fieldEnabledModuless" name="tx_extensionmanager_tools_extensionmanagerextensionmanager[config][enabledModules][value]" value="{$this->configuration['enabledModules']}" />
				</div>
EOF;

		return $output;
	}
}
