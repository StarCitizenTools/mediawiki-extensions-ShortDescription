function main() {
	var shortdesc = mw.config.get( 'wgShortDesc' ),
		tagline;

	if ( shortdesc ) {
		tagline = document.getElementById( 'siteSub' );
		tagline.classList.add( 'ext-shortdesc' );
		tagline.innerHTML = shortdesc;
	}
}

main();
