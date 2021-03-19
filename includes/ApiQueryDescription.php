<?php

namespace MediaWiki\Extension\ShortDescription;

use ApiQuery;
use ApiQueryBase;

/**
 * Extracted from WikiBase
 * See T184000 for related info
 */

/**
 * Provides a short description of the page.
 * The description may be taken from an upstream Wikibase instance, or from a parser function in
 * the article wikitext.
 *
 * Arguably this should be a separate extension so that it can be used on wikis without Wikibase
 * as well, but was initially implemented inside Wikibase for speed and convenience (T189154).
 *
 * @license GPL-2.0-or-later
 */
class ApiQueryDescription extends ApiQueryBase {

	/**
	 * @param ApiQuery $query
	 * @param string $moduleName
	 */
	public function __construct(
		ApiQuery $query,
		$moduleName
	) {
		parent::__construct( $query, $moduleName, 'desc' );
	}

	/**
	 * @inheritDoc
	 */
	public function execute() {
		$continue = $this->getParameter( 'continue' );

		$titlesByPageId = $this->getPageSet()->getGoodTitles();
		// Just in case we are dealing with titles from some very fast generator,
		// apply some limits as a sanity check.
		$limit = $this->getMain()->canApiHighLimits() ? self::LIMIT_BIG2 : self::LIMIT_BIG1;
		if ( $continue + $limit < count( $titlesByPageId ) ) {
			$this->setContinueEnumParameter( 'continue', $continue + $limit );
		}
		$titlesByPageId = array_slice( $titlesByPageId, $continue, $limit, true );

		$descriptionsByPageId = Hooks\HookUtils::getDescriptionsByPageId( $titlesByPageId );

		$this->addDataToResponse( array_keys( $titlesByPageId ),
			$descriptionsByPageId, $continue );
	}

	/**
	 * @param int[] $pageIds Page IDs, in the same order as returned by the ApiPageSet.
	 * @param string[] $descriptionsByPageId Descriptions from wikitext, as an
	 *   associative array of page ID
	 * @param int $continue The API request is being continued from this position.
	 */
	private function addDataToResponse(
		array $pageIds,
		array $descriptionsByPageId,
		$continue
	) {
		$result = $this->getResult();
		$i = 0;
		$fit = true;
		foreach ( $pageIds as $pageId ) {
			$path = [ 'query', 'pages', $pageId ];
			if ( array_key_exists( $pageId, $descriptionsByPageId ) ) {
				$fit = $result->addValue( $path, 'description', $descriptionsByPageId[$pageId] );
			}
			if ( !$fit ) {
				$this->setContinueEnumParameter( 'continue', $continue + $i );
				break;
			}
			$i++;
		}
	}

	/**
	 * @inheritDoc
	 */
	public function getCacheMode( $params ) {
		return 'public';
	}

	/**
	 * @inheritDoc
	 */
	protected function getAllowedParams() {
		return [
			'continue' => [
				self::PARAM_HELP_MSG => 'api-help-param-continue',
				self::PARAM_TYPE => 'integer',
				self::PARAM_DFLT => 0,
			],
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function getExamplesMessages() {
		return [
			'action=query&prop=description&titles=London'
			=> 'apihelp-query+description-example',
		];
	}

}
