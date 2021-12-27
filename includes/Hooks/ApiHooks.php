<?php
/**
 * ShortDescription api hooks
 *
 * @file
 * @ingroup Extensions
 * @license GPL-3.0-or-later
 */

declare( strict_types=1 );

namespace MediaWiki\Extension\ShortDescription\Hooks;

use ApiBase;
use ApiMain;
use FauxRequest;
use MediaWiki\Api\Hook\ApiOpenSearchSuggestHook;

class ApiHooks implements ApiOpenSearchSuggestHook {

	/**
	 * ApiOpenSearchSuggest hook handler
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ApiOpenSearchSuggest
	 *
	 * @param array $results Array of search results
	 */
	public function onApiOpenSearchSuggest( &$results ) {
		if ( !HookUtils::getConfig( 'ShortDescriptionExtendOpenSearchXml' ) || !count( $results ) ) {
			return;
		}

		$data = [];
		$pageIds = array_keys( $results );

		foreach ( array_chunk( $pageIds, ApiBase::LIMIT_SML1 ) as $chunk ) {
			$request = [
				'action' => 'query',
				'prop' => 'description',
				'pageids' => implode( '|', $chunk ),
			];

			$api = new ApiMain( new FauxRequest( $request ) );
			$api->execute();

			$data += (array)$api->getResult()->getResultData( [ 'query', 'pages' ] );
		}

		foreach ( $pageIds as $id ) {
			if ( isset( $data[$id]['description'] ) ) {
				$results[$id]['extract'] = $data[$id]['description'];
				$results[$id]['extract trimmed'] = false;
			}
		}

		$pageIds = array_keys( $results );
	}
}
