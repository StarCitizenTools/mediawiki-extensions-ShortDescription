<?php
/**
 * ShortDescription parser hooks
 *
 * @file
 * @ingroup Extensions
 * @license GPL-3.0-or-later
 */

declare( strict_types=1 );

namespace MediaWiki\Extension\ShortDescription\Hooks;

use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\Output\Hook\OutputPageParserOutputHook;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\ParserOutput;
use MediaWiki\Title\Title;

class ParserHooks implements
	OutputPageParserOutputHook,
	ParserFirstCallInitHook
{
	/**
	 * Register property for extensions or skins to use in Outputpage
	 *
	 * @param OutputPage $out
	 * @param ParserOutput $parserOutput ParserOutput instance being added in $out
	 */
	public function onOutputPageParserOutput( $out, $parserOutput ): void {
		$shortDesc = $parserOutput->getPageProperty( 'shortdesc' );

		// Return if tagline is not enabled
		if ( !HookUtils::getConfig( 'ShortDescriptionEnableTagline' ) ) {
			return;
		}

		$out->setProperty( 'shortdesc', $shortDesc );
		// Supply description to Minerva
		$out->setProperty( 'wgMFDescription', $shortDesc );
	}

	/**
	 * Register any render callbacks with the parser
	 *
	 * @param Parser $parser
	 * @return true
	 */
	public function onParserFirstCallInit( $parser ) {
		$parser->setFunctionHook(
			'MAG_GETSHORTDESC',
			[ self::class, 'rendershortdesc' ],
			Parser::SFH_NO_HASH
		);

		$parser->setFunctionHook(
			'MAG_SHORTDESC',
			[ self::class, 'handle' ],
			Parser::SFH_NO_HASH
		);

		return true;
	}

	/**
	 * Render the output of {{GETSHORTDESC}}.
	 *
	 * @param Parser $parser
	 * @param string $input
	 * @return string
	 */
	public static function rendershortdesc( Parser $parser, $input = '' ) {
		$output = '';

		// If no title is set then use current page
		if ( $input ) {
			$title = Title::newFromText( $input );
		} else {
			$title = $parser->getPage();
		}

		// Bail if the title cannot be parsed
		// See https://issue-tracker.miraheze.org/T13055
		if ( $title === null ) {
			return $output;
		}

		// Check if shortdesc exists, render if exist
		$shortDesc = HookUtils::getShortDescription( $title );
		if ( $shortDesc !== false ) {
			$output = $shortDesc;
		}

		return $output;
	}

	/**
	 * Extracted from WikiBase
	 * See T184000 for related info
	 */

	/**
	 * Parser function callback
	 *
	 * @param Parser $parser
	 * @param string $shortDesc Short description of the current page, as plain text.
	 *
	 * @return string
	 */
	public static function handle( Parser $parser, $shortDesc ) {
		$handler = self::factory();
		$handler->doHandle( $parser, $shortDesc );
		return '';
	}

	/**
	 * @return self
	 */
	private static function factory() {
		return new self();
	}

	/**
	 * Validates a short description.
	 * Valid descriptions are not empty (contain something other than whitespace/punctuation).
	 *
	 * @param string $shortDesc Short description of the current page, as plain text.
	 *
	 * @return bool
	 */
	public function isValid( $shortDesc ) {
		return !preg_match( '/^[\s\p{P}\p{Z}]*$/u', $shortDesc );
	}

	/**
	 * Sanitizes a short description by converting it into plaintext.
	 *
	 * Note that the sanitized description can still contain HTML (that was encoded as entities in
	 * the original) as there is no reason why someone shouldn't mention HTML tags in a description.
	 * That means the sanitized value is actually less safe for HTML inclusion than the original
	 * one (can contain <script> tags)! It is clients' responsibility to handle it safely.
	 *
	 * @param string $shortDesc Short description of the current page, as HTML.
	 *
	 * @return string Plaintext of description.
	 */
	public function sanitize( $shortDesc ) {
		// Remove accidental formatting - descriptions are plaintext.
		$shortDesc = strip_tags( $shortDesc );
		// Unescape - clients are not necessarily HTML-based and using HTML tags as part of
		// the descript (i.e. with <nowiki> or such) should be possible.
		$shortDesc = html_entity_decode( $shortDesc, ENT_QUOTES, 'utf-8' );
		// Remove newlines, tabs and other weird whitespace
		$shortDesc = preg_replace( '/\s+/', ' ', $shortDesc );
		// Get rid of leading/trailing space - no valid usecase for it, easy for it to go unnoticed
		// in HTML, and clients might display the description in an environment that does not
		// ignore spaces like HTML does.
		return trim( $shortDesc );
	}

	/**
	 * Parser function
	 *
	 * @param Parser $parser
	 * @param string $shortDesc Short description of the current page, as plain text.
	 *
	 * @return void
	 */
	public function doHandle( Parser $parser, $shortDesc ) {
		$shortDesc = $this->sanitize( $shortDesc );
		if ( $this->isValid( $shortDesc ) ) {
			$parserOutput = $parser->getOutput();
			$parserOutput->setPageProperty( 'shortdesc', $shortDesc );
			$parser->addTrackingCategory( 'shortdescription-category' );
		}
	}
}
