function main() {
	const shortdesc = mw.config.get( 'wgShortDesc' );

	if ( !shortdesc ) {
		return;
	}

	mw.util.addSubtitle( shortdesc );
}

main();
