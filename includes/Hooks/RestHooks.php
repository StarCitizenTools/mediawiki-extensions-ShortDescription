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
use MediaWiki\Title\Title;

class RestHooks implements SearchResultProvideDescriptionHook {

	/**
	 * Look up descriptions for a set of pages.
	 *
	 * @param Title[] $titles Titles to look up.
	 * @return array<int,?string> Associative array of page ID => description (or null).
	 */
	public function getDescriptions( array $titles ): array {
		$pageIds = array_map( static fn ( Title $title ): int => $title->getArticleID(), $titles );
		$titlesByPageId = array_combine( $pageIds, $titles );

		$descriptions = HookUtils::getDescriptionsByPageId( $titlesByPageId );

		// Restore original sort order.
		$pageIds = array_intersect( $pageIds, array_keys( $descriptions ) );
		return array_replace( array_fill_keys( $pageIds, null ), $descriptions );
	}

	/**
	 * Update Search Results with descriptions for the search engine.
	 *
	 * @param array<int,object> $pageIdentities Array of pageId => SearchResultPageIdentity.
	 * @param array<int,?string> &$descriptions Output array of pageId => description (or null).
	 */
	public function onSearchResultProvideDescription(
		array $pageIdentities,
		&$descriptions
	): void {
		$pageIdTitles = array_map(
			static fn ( $identity ): Title => Title::makeTitle( $identity->getNamespace(), $identity->getDBkey() ),
			$pageIdentities
		);

		foreach ( $this->getDescriptions( $pageIdTitles ) as $pageId => $description ) {
			$descriptions[$pageId] = $description;
		}
	}
}
