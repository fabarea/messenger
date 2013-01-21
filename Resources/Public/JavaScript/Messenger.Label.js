"use strict";

/** @namespace Media */

/**
 * Language object
 *
 * @type {Object}
 */
Messenger.Label = {

	/**
	 * array containing all labels
	 */
	labels: Messenger._labels,

	/**
	 *
	 * @param key
	 * @return string
	 */
	get: function (key) {
		return this.labels[key];
	}
};
