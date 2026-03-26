<?php
/*
Plugin Name:  Reo Tracking
Plugin URI:   https://github.com/michaelschwartz/yourls_plugin_reo
Description:  Injects Reo.dev analytics on every short URL redirect via an intermediate page.
Version:      1.0.0
Author:       Michael Schwartz
Author URI:   https://github.com/michaelschwartz
*/

yourls_add_action( 'redirect_shorturl', 'reotracking_intercept' );

/**
 * Intercept short URL redirects: serve intermediate HTML with Reo, then forward to target.
 *
 * @param string $url     Target URL from YOURLS.
 * @param string $keyword Short keyword (unused; matches hook arity).
 */
function reotracking_intercept( $url, $keyword = '' ) {
	if ( ! is_string( $url ) || $url === '' ) {
		yourls_status_header( 400 );
		die();
	}

	$safe_html = htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' );
	$safe_js   = json_encode( $url );

	header( 'Content-Type: text/html; charset=UTF-8' );
	yourls_status_header( 200 );

	?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="refresh" content="5;url=<?php echo $safe_html; ?>">
	<title>Redirecting…</title>
	<script type="text/javascript">!function(){var e,t,n;e="d879136bc2a2e75",t=function(){Reo.init({clientID:"d879136bc2a2e75"})},(n=document.createElement("script")).src="https://static.reo.dev/"+e+"/reo.js",n.defer=!0,n.onload=t,document.head.appendChild(n)}();</script>
	<script type="text/javascript">
(function () {
	var dest = <?php echo $safe_js; ?>;
	function go() {
		window.location = dest;
	}
	function chainReoOnload() {
		var scripts = document.head.getElementsByTagName('script');
		var i, s, prev;
		for (i = scripts.length - 1; i >= 0; i--) {
			s = scripts[i];
			if (s.src && s.src.indexOf('static.reo.dev/') !== -1) {
				prev = s.onload;
				(function (node, previous) {
					node.onload = function (ev) {
						if (typeof previous === 'function') {
							previous.call(node, ev);
						}
						go();
					};
				})(s, prev);
				return true;
			}
		}
		return false;
	}
	if (!chainReoOnload()) {
		setTimeout(function () {
			if (!chainReoOnload()) {
				go();
			}
		}, 0);
	}
})();
	</script>
</head>
<body>
	<p>Redirecting, please wait…</p>
</body>
</html>
	<?php
	die();
}
