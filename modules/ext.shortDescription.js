function main() {
	var shortdesc = mw.config.get( 'wgShortDesc' ),
		contentSub;

	if ( shortdesc ) {
		contentSub = document.getElementById( 'contentSub' );
		// Wikipedia uses shortdescription class
		// Added for gadgets and extension compatibility
		contentSub.classList.add( 'ext-shortdesc', 'shortdescription' );
		contentSub.innerHTML = mw.html.escape( shortdesc );
	}
}

main();
