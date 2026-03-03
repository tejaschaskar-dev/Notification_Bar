/**
 * Simple Notification Bar — Frontend Script
 *
 * Responsibilities:
 *   1. Add the `snb-bar-visible` class to <body> so the page
 *      content is pushed down and not hidden behind the fixed bar.
 *   2. Handle the dismiss (×) button click: hide the bar and
 *      remove the body offset class.
 *
 * This file is loaded with `strategy: defer` so the DOM is
 * always ready when this code runs — no DOMContentLoaded wrapper needed.
 *
 * Vanilla JS only — no jQuery dependency.
 *
 * @package SimpleNotificationBar
 */

( function () {
	'use strict';

	// ── Grab references ──────────────────────────────────────────
	const bar         = document.getElementById( 'snb-notification-bar' );
	const dismissBtn  = bar ? bar.querySelector( '.snb-dismiss-btn' ) : null;

	// Guard: if the bar element is absent, do nothing.
	// (Happens when visibility is off — the bar is never rendered.)
	if ( ! bar ) {
		return;
	}

	// ── Step 1: Push body content down ───────────────────────────
	// We add this class here (via JS) rather than outputting it in
	// PHP so that if JS is disabled the body still renders normally
	// (the bar will overlap slightly, but content remains accessible).
	document.body.classList.add( 'snb-bar-visible' );

	// ── Step 2: Dismiss handler ───────────────────────────────────
	if ( dismissBtn ) {
		dismissBtn.addEventListener( 'click', function () {
			// Hide the bar with a quick CSS transition defined in frontend.css.
			bar.classList.add( 'snb-hidden' );

			// Remove the body offset so the page content slides back up cleanly.
			document.body.classList.remove( 'snb-bar-visible' );

			// Move focus to the <body> so keyboard users aren't left stranded
			// on a now-invisible element.
			document.body.focus();
		} );
	}
}() );
