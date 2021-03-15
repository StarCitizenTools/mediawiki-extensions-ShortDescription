<?php
/**
 * ShortDescription extension hooks
 *
 * @file
 * @ingroup Extensions
 * @license MIT
 */

namespace MediaWiki\Extension\ShortDescription\Hooks;

use PageProps;

class HookUtils {
	
	/**
	 * Page_props key for short description.
	 */
	public const PROP_NAME = 'shortdesc';

	/**
	 * Returns pageprops array for short description
	 * @param Title $title Title to get page image for
	 * @return array PageProps for short description
	 */
	public static function getPageProps( $title ) {
		return PageProps::getInstance()->getProperties( $title, self::PROP_NAME );
	}

	/**
	 * Returns short description for a given title
	 * @param Title $title Title to get page image for
	 * @return string short description
	 */
	public static function getShortDescription( $title ) {
		$shortDesc = implode( '', self::getPageProps( $title ) );
		return $shortDesc;
	}

	/**
	 * Check if the short description are available for a given title
	 * @param Title $title
	 * @return bool
	 */
	public static function isAvailableForTitle( $title ) : bool {
		// Only wikitext pages
		if ( $title->getContentModel() !== CONTENT_MODEL_WIKITEXT ) {
			return false;
		}
		$hasShortdesc = isset( self::getPageProps( $title )[ $title->getArticleId() ] );

		return $hasShortdesc;
	}
}