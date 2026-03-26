# yourls_plugin_reo

YOURLS plugin that serves a short HTML intermediate page on every short-URL hit: it loads the [Reo.dev](https://reo.dev) snippet, then redirects the visitor to the original target. See `.kiro/specs/yourls-reo-tracking/` for requirements and design.

## Install

1. Copy the folder `user/plugins/yourls-reo-tracking/` into your YOURLS install as `user/plugins/yourls-reo-tracking/` (so `plugin.php` lives at `user/plugins/yourls-reo-tracking/plugin.php`).
2. In the YOURLS admin, activate **Reo Tracking**.

Deactivating the plugin restores YOURLS’s normal redirect behavior. No database changes are made.
