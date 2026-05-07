<?php
/**
 * ShortDescription extension hooks
 *
 * @file
 * @ingroup Extensions
 * @license MIT
 */

declare( strict_types=1 );

namespace MediaWiki\Extension\ShortDescription\Hooks;

use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;

class HookUtils {

	/**
	 * Page_props key for short description.
	 */
	public const PROP_NAME = 'shortdesc';

	/**
	 * Returns pageprops array for short description.
	 *
	 * @param Title|Title[] $title Single Title, or an array of page ID => Title.
	 * @return array<int,string> PageProps for short description, keyed by page ID.
	 */
	public static function getPageProps( Title|array $title ): array {
		return MediaWikiServices::getInstance()->getPageProps()->getProperties( $title, self::PROP_NAME );
	}

	/**
	 * Returns short description for a given title.
	 */
	public static function getShortDescription( Title $title ): string {
		return implode( '', self::getPageProps( $title ) );
	}

	/**
	 * Look up descriptions (stored in the page wikitext via parser function) for a set of pages.
	 *
	 * @param Title[] $titlesByPageId Associative array of page ID => Title object.
	 * @return string[] Associative array of page ID => description.
	 */
	public static function getDescriptionsByPageId( array $titlesByPageId ): array {
		if ( !$titlesByPageId ) {
			return [];
		}
		return self::getPageProps( $titlesByPageId );
	}

	/**
	 * Check if the short description is available for a given title.
	 */
	public static function isAvailableForTitle( Title $title ): bool {
		// Only wikitext pages
		if ( $title->getContentModel() !== CONTENT_MODEL_WIKITEXT ) {
			return false;
		}
		return isset( self::getPageProps( $title )[ $title->getArticleId() ] );
	}

	/**
	 * Returns ShortDescription config value.
	 */
	public static function getConfig( string $name ): mixed {
		return MediaWikiServices::getInstance()
			->getConfigFactory()
			->makeConfig( 'shortdescription' )
			->get( $name );
	}
}
