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

use MediaWiki\Hook\OutputPageParserOutputHook;
use MediaWiki\Hook\ParserFirstCallInitHook;
use Parser;
use ParserOutput;
use Title;

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
	public function onOutputPageParserOutput( $out, $parserOutput ) : void {
		// TODO: Remove when we bump requirements
		if ( method_exists( ParserOutput::class, 'getPageProperty' ) ) {
			// ParserOutput::getPageProperty is avaliable since MW 1.38
			$shortDesc = $parserOutput->getPageProperty( 'shortdesc' );
		} else {
			// ParserOutput::getProperty is deprecated in MW 1.38
			$shortDesc = $parserOutput->getProperty( 'shortdesc' );
		}

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
			$title = $parser->getTitle();
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
		$handler = self::newFromGlobalState();
		$handler->doHandle( $parser, $shortDesc );
		return '';
	}

	/**
	 * @return self
	 */
	private static function newFromGlobalState() {
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
	 * No effort is made to handle trickier cases like <pre> correctly as there is no legitimate
	 * reason to use anything like that in {{SHORTDESC:...}}.
	 *
	 * @param string $shortDesc Short description of the current page, as HTML.
	 *
	 * @return string Plaintext of description.
	 */
	public function sanitize( $shortDesc ) {
		return trim( html_entity_decode( strip_tags( $shortDesc ), ENT_QUOTES, 'utf-8' ) );
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
			// TODO: Remove when we bump requirements
			if ( method_exists( ParserOutput::class, 'setPageProperty' ) ) {
				// ParserOutput::setPageProperty is avaliable since MW 1.38
				$parserOutput->setPageProperty( 'shortdesc', $shortDesc );
			} else {
				// ParserOutput::setProperty is deprecated in MW 1.38
				$parserOutput->setProperty( 'shortdesc', $shortDesc );
			}
			$parser->addTrackingCategory( 'shortdescription-category' );
		}
	}
}
