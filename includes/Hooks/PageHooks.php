<?php
/**
 * ShortDescription page hooks
 *
 * @file
 * @ingroup Extensions
 * @license GPL-3.0-or-later
 */

declare( strict_types=1 );

namespace MediaWiki\Extension\ShortDescription\Hooks;

use MediaWiki\Hook\BeforePageDisplayHook;

class PageHooks implements BeforePageDisplayHook {

	/**
	 * List of skins that has native PHP support for short description
	 */
	private const NATIVE_SKINS = [ 'citizen', 'minerva' ];

	/**
	 * Add the required javascript to replace the tagline with shortdesc
	 * @param OutputPage $out OutputPage
	 * @param Skin $skin
	 */
	public function onBeforePageDisplay( $out, $skin ) : void {
		$title = $out->getTitle();

		// Return if tagline is not enabled
		if ( !HookUtils::getConfig( 'ShortDescriptionEnableTagline' ) ) {
			return;
		}

		// Load module if the page is suitable
		if ( HookUtils::isAvailableForTitle( $title ) ) {
			// Load module if the skin has no native support
			if ( !in_array( $skin->getSkinName(), self::NATIVE_SKINS ) ) {
				$out->addJsConfigVars( 'wgShortDesc', HookUtils::getShortDescription( $title ) );
				$out->addModules( [
					'ext.shortDescription'
				] );
			}
		}
	}
}
