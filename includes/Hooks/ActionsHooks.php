<?php
/**
 * ShortDescription actions hooks
 * Based on PageImages
 *
 * @file
 * @ingroup Extensions
 * @license GPL-3.0-or-later
 */

declare( strict_types=1 );

namespace MediaWiki\Extension\ShortDescription\Hooks;

use MediaWiki\Hook\InfoActionHook;
use Title;

class ActionsHooks implements InfoActionHook {

	/**
	 * page_props key.
	 */
	public const PROPERTY_NAME = 'shortdesc';

	/**
	 * Returns short description for a given title
	 *
	 * @param Title $title Title to get page image for
	 *
	 * @return File|bool
	 */
	public static function getShortDescription( Title $title ) {
		$shortdescText = '';

		// Do not query for special pages or other titles never in the database
		if ( !$title->canExist() ) {
			return false;
		}

		if ( !$title->exists() ) {
			// No page id to select from
			return false;
		}

		$dbr = wfGetDB( DB_REPLICA );
		$shortdescText = $dbr->selectField( 'page_props',
			'pp_value',
			[
				'pp_page' => $title->getArticleID(),
				'pp_propname' => self::PROPERTY_NAME
			],
			__METHOD__,
			[ 'ORDER BY' => 'pp_propname' ]
		);

		return $shortdescText;
	}

	/**
	 * InfoAction hook handler, adds the short description to the info=action page
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/InfoAction
	 *
	 * @param IContextSource $context Context, used to extract the title of the page
	 * @param array[] &$pageInfo Auxillary information about the page.
	 */
	public function onInfoAction( $context, &$pageInfo ) {
		$shortdesc = self::getShortDescription( $context->getTitle() );
		if ( !$shortdesc ) {
			// The page has no short description
			return;
		}

		$pageInfo['header-basic'][] = [
			$context->msg( 'shortdescription-info-label' ),
			$shortdesc
		];
	}
}
