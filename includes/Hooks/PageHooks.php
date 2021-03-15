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
use PageProps;
use Title;

class PageHooks implements BeforePageDisplayHook {
	/**
	 * Add the required javascript to replace the tagline with shortdesc
	 * @param OutputPage $out OutputPage
	 * @param Skin $skin
	 */	
	public function onBeforePageDisplay( $out, $skin ) : void {
		$title = $out->getTitle();

		// Load module if the page is suitable
		if ( self::isAvailableForTitle( $title ) ) {
			$shortdesc = implode( '', PageProps::getInstance()->getProperties( $title, 'shortdesc' ) );
			$out->addJsConfigVars( 'wgShortDesc', $shortdesc );
			$out->addModules( [
				'ext.shortDescription'
			] );
		}
	}

	/**
	 * Check if the tools are available for a given title
	 * @param Title $title
	 * @return bool
	 */
	private function isAvailableForTitle( Title $title ) : bool {
		// Only wikitext pages
		if ( $title->getContentModel() !== CONTENT_MODEL_WIKITEXT ) {
			return false;
		}

		// Check if there is any short descriptions
		$props = PageProps::getInstance()->getProperties( $title, 'shortdesc' );
		$hasShortdesc = isset( $props[ $title->getArticleId() ] );

		return $hasShortdesc;
	}
}
