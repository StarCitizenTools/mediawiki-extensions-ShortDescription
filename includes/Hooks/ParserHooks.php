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
use MediaWiki\Output\OutputPage;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\ParserOutput;
use MediaWiki\Title\Title;

class ParserHooks implements
	OutputPageParserOutputHook,
	ParserFirstCallInitHook
{
	/**
	 * Register property for extensions or skins to use in OutputPage.
	 *
	 * @param OutputPage $out
	 * @param ParserOutput $parserOutput
	 */
	public function onOutputPageParserOutput( $out, $parserOutput ): void {
		if ( !HookUtils::getConfig( 'ShortDescriptionEnableTagline' ) ) {
			return;
		}

		$shortDesc = $parserOutput->getPageProperty( 'shortdesc' );
		$out->setProperty( 'shortdesc', $shortDesc );
		// Supply description to Minerva
		$out->setProperty( 'wgMFDescription', $shortDesc );
	}

	/**
	 * Register render callbacks with the parser.
	 *
	 * @param Parser $parser
	 */
	public function onParserFirstCallInit( $parser ) {
		$parser->setFunctionHook(
			'MAG_GETSHORTDESC',
			self::rendershortdesc( ... ),
			Parser::SFH_NO_HASH
		);

		$parser->setFunctionHook(
			'MAG_SHORTDESC',
			self::handle( ... ),
			Parser::SFH_NO_HASH
		);
	}

	/**
	 * Render the output of {{GETSHORTDESC}}.
	 */
	public static function rendershortdesc( Parser $parser, string $input = '' ): string {
		$title = $input !== '' ? Title::newFromText( $input ) : $parser->getTitle();

		// Bail if the title cannot be parsed
		// See https://issue-tracker.miraheze.org/T13055
		if ( $title === null ) {
			return '';
		}

		return HookUtils::getShortDescription( $title );
	}

	/**
	 * Extracted from WikiBase.
	 * See T184000 for related info.
	 */

	/**
	 * Parser function callback for {{SHORTDESC:...}}.
	 *
	 * @param Parser $parser
	 * @param string $shortDesc Short description of the current page, as plain text.
	 */
	public static function handle( Parser $parser, string $shortDesc ): string {
		( new self() )->doHandle( $parser, $shortDesc );
		return '';
	}

	/**
	 * Validate a short description.
	 * Valid descriptions contain something other than whitespace/punctuation.
	 */
	public function isValid( string $shortDesc ): bool {
		return !preg_match( '/^[\s\p{P}\p{Z}]*$/u', $shortDesc );
	}

	/**
	 * Sanitize a short description by converting it into plaintext.
	 *
	 * Note that the sanitized description can still contain HTML (that was encoded as entities in
	 * the original) — there is no reason why someone shouldn't mention HTML tags in a description.
	 * That means the sanitized value is actually less safe for HTML inclusion than the original
	 * one (can contain <script> tags). It is the client's responsibility to handle it safely.
	 *
	 * @param string $shortDesc Short description of the current page, as HTML.
	 * @return string Plaintext of description.
	 */
	public function sanitize( string $shortDesc ): string {
		// Remove accidental formatting — descriptions are plaintext.
		$shortDesc = strip_tags( $shortDesc );
		// Unescape — clients are not necessarily HTML-based and using HTML tags as part of the
		// description (i.e. with <nowiki> or such) should be possible.
		$shortDesc = html_entity_decode( $shortDesc, ENT_QUOTES, 'utf-8' );
		// Remove newlines, tabs and other weird whitespace.
		$shortDesc = preg_replace( '/\s+/', ' ', $shortDesc );
		// Get rid of leading/trailing space — no valid use case for it, easy for it to go unnoticed
		// in HTML, and clients might display the description in an environment that does not
		// ignore spaces like HTML does.
		return trim( $shortDesc );
	}

	/**
	 * Parser function implementation: store the description as a page property
	 * and add the tracking category.
	 */
	public function doHandle( Parser $parser, string $shortDesc ): void {
		$shortDesc = $this->sanitize( $shortDesc );
		if ( $this->isValid( $shortDesc ) ) {
			$parser->getOutput()->setPageProperty( 'shortdesc', $shortDesc );
			$parser->addTrackingCategory( 'shortdescription-category' );
		}
	}
}
