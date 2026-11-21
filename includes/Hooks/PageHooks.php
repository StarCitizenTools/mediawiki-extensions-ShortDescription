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
use MediaWiki\Html\Html;
use MediaWiki\Output\OutputPage;
use Skin;

class PageHooks implements BeforePageDisplayHook {

	/**
	 * List of skins that has native PHP support for short description
	 */
	private const NATIVE_SKINS = [ 'citizen', 'minerva' ];

	/**
	 * Add the short description to the page subtitle.
	 *
	 * @param OutputPage $out
	 * @param Skin $skin
	 */
	public function onBeforePageDisplay( $out, $skin ): void {
		if ( !HookUtils::getConfig( 'ShortDescriptionEnableTagline' ) ) {
			return;
		}

		$title = $out->getTitle();
		if ( !HookUtils::isAvailableForTitle( $title ) ) {
			return;
		}

		if ( in_array( $skin->getSkinName(), self::NATIVE_SKINS, true ) ) {
			return;
		}

		// The subtitle container carries the user language, but the description is page content
		$pageLang = $title->getPageLanguage();
		$out->addSubtitle( Html::element( 'span', [
			// Wikipedia uses the shortdescription class, kept for gadget compatibility
			'class' => 'ext-shortdesc shortdescription',
			'lang' => $pageLang->getHtmlCode(),
			'dir' => $pageLang->getDir(),
		], HookUtils::getShortDescription( $title ) ) );
	}
}
