function main() {
	const shortdesc = mw.config.get( 'wgShortDesc' );

	if ( !shortdesc ) {
		return;
	}

	mw.util.addSubtitle( mw.html.escape( shortdesc ) );
}

main();
