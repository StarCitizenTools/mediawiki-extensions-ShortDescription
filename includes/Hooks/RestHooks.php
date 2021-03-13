<?php
/**
 * ShortDescription rest hooks
 * Based on Wikibase extension
 *
 * @file
 * @ingroup Extensions
 * @license GPL-3.0-or-later
 */

declare( strict_types=1 );

namespace MediaWiki\Extension\ShortDescription\Hooks;

use MediaWiki\Rest\Entity\SearchResultPageIdentity;
use MediaWiki\Rest\Hook\SearchResultProvideDescriptionHook;
use PageProps;
use Title;


class RestHooks implements SearchResultProvideDescriptionHook {

	/**
	 * page_props key.
	 */
	public const PROPERTY_NAME = 'shortdesc';

	/** @var PageProps */
	private $pageProps;

	/**
	 * Look up descriptions for a set of pages.
	 * @param Title[] $titles Titles to look up (will be loaded).
	 * @param array|string $sources One or both of the DescriptionLookup::SOURCE_* constants.
	 *   When an array is provided, the second element will be used as fallback.
	 * @param null $actualSources Will be set to an associative array of page ID => SOURCE_*,
	 *   indicating where each description came from, or null if no description was found.
	 * @return string[] Associative array of page ID => description. Pages with no description
	 *   will be omitted.
	 */
	public function getDescriptions( array $titles ) {
		$pageIds = array_map( function ( Title $title ) {
			return $title->getArticleID();
		}, $titles );
		$titlesByPageId = array_combine( $pageIds, $titles );

		$descriptions = [];
		$descriptions += $this->getLocalDescriptions( $titlesByPageId );

		// Restore original sort order.
		$pageIds = array_intersect( $pageIds, array_keys( $descriptions ) );
		$descriptions = array_replace( array_fill_keys( $pageIds, null ), $descriptions );

		return $descriptions;
	}

	/**
	 * Look up local descriptions (stored in the page wikitext via parser function) for a set of pages.
	 * @param Title[] $titlesByPageId Associative array of page ID => Title object.
	 * @return string[] Associative array of page ID => description.
	 */
	private function getLocalDescriptions( array $titlesByPageId ) {
		$pageProps = PageProps::getInstance();

		if ( !$titlesByPageId ) {
			return [];
		}
		return $pageProps->getProperties( $titlesByPageId, self::PROPERTY_NAME );
	}

	/**
	 * Used to update Search Results with descriptions for Search Engine.
	 */
	public function onSearchResultProvideDescription( 
		array $pageIdentities,
		&$descriptions
	):void {
		$pageIdTitles = array_map( function ( SearchResultPageIdentity $identity ) {
			return Title::makeTitle( $identity->getNamespace(), $identity->getDBkey() );
		}, $pageIdentities );

		$newDescriptions = $this->getDescriptions( $pageIdTitles );

		foreach ( $newDescriptions as $pageId => $description ) {
			$descriptions[$pageId] = $description;
		}
	}
}
