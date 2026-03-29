<?php
/*
Plugin Name:  Reo Tracking
Plugin URI:   https://github.com/michaelschwartz/yourls_plugin_reo
Description:  Injects Reo.dev analytics on every short URL redirect via an intermediate page.
Version:      1.0.3
Author:       Michael Schwartz
Author URI:   https://github.com/michaelschwartz
*/

yourls_add_action('redirect_shorturl', 'reotracking_intercept');

function reotracking_intercept($args) {

    /*
    DEBUG BLOCK
    Uncomment when troubleshooting hook behavior
    */
    /*
    error_log('REO DEBUG: plugin loaded');

    if (!is_array($args)) {
        error_log('REO DEBUG: args not array');
    } else {
        error_log('REO DEBUG: args received: ' . print_r($args, true));
    }
    */

    if (!is_array($args) || empty($args[0]) || !is_string($args[0])) {

        /*
        DEBUG
        error_log('REO DEBUG: invalid args');
        */

        return;
    }

    $url = $args[0];
    $keyword = isset($args[1]) ? $args[1] : '';

    /*
    DEBUG
    error_log('REO DEBUG: intercept fired | keyword=' . $keyword . ' | url=' . $url);
    */

    if (headers_sent()) {

        /*
        DEBUG
        error_log('REO DEBUG: headers already sent');
        */

        return;
    }

    $safeHtml = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    $safeJs   = json_encode($url, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    yourls_status_header(200);

    header('Content-Type: text/html; charset=UTF-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');

    echo '<!DOCTYPE html>';
    echo '<html lang="en">';
    echo '<head>';

    echo '<meta charset="UTF-8">';

    /*
    HTML fallback redirect
    Keep slightly longer than JS redirect
    */
    echo '<meta http-equiv="refresh" content="1;url=' . $safeHtml . '">';

    echo '<title>Redirecting…</title>';

    echo '<script>';
    echo '(function(){';

    echo 'var dest = ' . $safeJs . ';';
    echo 'var redirected = false;';

    echo 'function go(){';
    echo '    if (redirected) return;';
    echo '    redirected = true;';
    echo '    window.location.replace(dest);';
    echo '}';

    /*
    fallback redirect if REO blocked or slow
    */
    echo 'setTimeout(go, 800);';

    echo 'var s = document.createElement("script");';
    echo 's.src = "https://static.reo.dev/d879136bc2a2e75/reo.js";';
    echo 's.defer = true;';

    echo 's.onload = function(){';

    /*
    DEBUG
    console.log("REO script loaded");
    */

    echo '    try {';

    echo '        if (window.Reo) {';

    echo '            Reo.init({clientID:"d879136bc2a2e75"});';

    /*
    DEBUG
    console.log("REO init OK");
    */

    echo '        }';

    echo '    } catch (e) {';

    /*
    DEBUG
    console.log("REO init error:", e);
    */

    echo '    }';

    echo '    go();';
    echo '};';

    echo 's.onerror = function(){';

    /*
    DEBUG
    console.log("REO script failed to load");
    */

    echo '    go();';
    echo '};';

    echo 'document.head.appendChild(s);';

    echo '})();';
    echo '</script>';

    echo '</head>';

    echo '<body>';

    echo '<p>Redirecting…</p>';

    echo '</body>';
    echo '</html>';

    exit;
}
