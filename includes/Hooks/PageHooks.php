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
	 * Add the required javascript to replace the tagline with shortdesc
	 * @param OutputPage $out OutputPage
	 * @param Skin $skin
	 */	
	public function onBeforePageDisplay( $out, $skin ) : void {
		$title = $out->getTitle();

		// Load module if the page is suitable
		if ( HookUtils::isAvailableForTitle( $title ) ) {
			$out->addJsConfigVars( 'wgShortDesc', HookUtils::getShortDescription( $title ) );
			$out->addModules( [
				'ext.shortDescription'
			] );
		}
	}
}
