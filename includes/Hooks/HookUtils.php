<?php
/**
 * ShortDescription extension hooks
 *
 * @file
 * @ingroup Extensions
 * @license MIT
 */

namespace MediaWiki\Extension\ShortDescription\Hooks;

use MediaWiki\MediaWikiServices;
use PageProps;

class HookUtils {

	/**
	 * Page_props key for short description.
	 */
	public const PROP_NAME = 'shortdesc';

	/**
	 * Returns pageprops array for short description
	 * @param Title $title Title to get short description for
	 * @return array PageProps for short description
	 */
	public static function getPageProps( $title ) {
		// TODO: Remove when we bump requirements
		if ( method_exists( MediaWikiServices::class, 'getPageProps' ) ) {
			// PMediaWikiServices::getPageProps is avaliable since MW 1.36
			return MediaWikiServices::getInstance()->getPageProps()->getProperties( $title, self::PROP_NAME );
		} else {
			// PageProps::getInstance is deprecated in MW 1.38
			return PageProps::getInstance()->getProperties( $title, self::PROP_NAME );
		}
	}

	/**
	 * Returns short description for a given title
	 * @param Title $title Title to get short description for
	 * @return string short description
	 */
	public static function getShortDescription( $title ) {
		$shortDesc = implode( '', self::getPageProps( $title ) );
		return $shortDesc;
	}

	/**
	 * Look up descriptions (stored in the page wikitext via parser function) for a set of pages.
	 * @param Title[] $titlesByPageId Associative array of page ID => Title object.
	 * @return string[] Associative array of page ID => description.
	 */
	public static function getDescriptionsByPageId( array $titlesByPageId ) {
		if ( !$titlesByPageId ) {
			return [];
		}
		return self::getPageProps( $titlesByPageId );
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

	/**
	 * Returns ShortDescription config value
	 * @param string $name config name
	 * @return string config value
	 */
	public static function getConfig( $name ) {
		$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'shortdescription' );
		$configValue = $config->get( $name );

		return $configValue;
	}
}
