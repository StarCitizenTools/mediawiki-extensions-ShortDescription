'use strict';

const { defineConfig, globalIgnores } = require( 'eslint/config' );
const wikimedia = require( 'eslint-config-wikimedia/flat' );

module.exports = defineConfig( [
	globalIgnores( [
		'i18n/',
		'vendor/'
	] ),

	...wikimedia( 'client', 'jquery', 'mediawiki' ),
	{
		rules: {
			'one-var': 'off',
			// ResourceLoader's `packageFiles` do not require wrapping, but the
			// `module` option is only available in ES6+.
			'no-implicit-globals': 'off'
		}
	}
] );
