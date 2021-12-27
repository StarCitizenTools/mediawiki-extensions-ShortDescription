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

use MediaWiki\Rest\Hook\SearchResultProvideDescriptionHook;
use Title;

class RestHooks implements SearchResultProvideDescriptionHook {

	/**
	 * Look up descriptions for a set of pages.
	 * @param Title[] $titles Titles to look up (will be loaded).
	 * @return string[] Associative array of page ID => description. Pages with no description
	 *   will be omitted.
	 */
	public function getDescriptions( array $titles ) {
		$pageIds = array_map( function ( Title $title ) {
			return $title->getArticleID();
		}, $titles );
		$titlesByPageId = array_combine( $pageIds, $titles );

		$descriptions = [];
		$descriptions += HookUtils::getDescriptionsByPageId( $titlesByPageId );

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
		if ( !$titlesByPageId ) {
			return [];
		}
		return HookUtils::getPageProps( $titlesByPageId );
	}

	/**
	 * Used to update Search Results with descriptions for Search Engine.
	 * @param array	$pageIdentities	Array (string=>SearchResultPageIdentity) where key is pageId
	 * @param array $descriptions Output array (string=>string|null)
	 * where key is pageId and value is either a description for given page or null
	 */
	public function onSearchResultProvideDescription(
		array $pageIdentities,
		&$descriptions
	):void {
		$pageIdTitles = array_map( function ( $identity ) {
			return Title::makeTitle( $identity->getNamespace(), $identity->getDBkey() );
		}, $pageIdentities );

		$newDescriptions = $this->getDescriptions( $pageIdTitles );

		foreach ( $newDescriptions as $pageId => $description ) {
			$descriptions[$pageId] = $description;
		}
	}
}
