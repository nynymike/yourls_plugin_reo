# Implementation Plan: yourls-reo-tracking

## Overview

Implement a single-file YOURLS plugin that intercepts short URL redirects and serves an intermediate HTML5 page with the Reo.dev tracking snippet before forwarding the visitor to the Target_URL.

## Tasks

- [ ] 1. Create plugin file with header and hook registration
  - Create `user/plugins/yourls-reo-tracking/plugin.php`
  - Add the YOURLS plugin header comment block with `Plugin Name`, `Plugin URI`, `Description`, `Version`, `Author`, and `Author URI` fields
  - Register the `redirect_shorturl` action hook via `yourls_add_action('redirect_shorturl', 'reotracking_intercept')`
  - _Requirements: 1.1, 1.2, 1.3_

- [ ] 2. Implement the redirect intercept callback
  - [ ] 2.1 Implement `reotracking_intercept(string $url, string $title): void`
    - Validate `$url` is a non-empty string; call `yourls_status_header(400)` and `die()` if invalid
    - Compute `$safe_html` via `htmlspecialchars($url, ENT_QUOTES, 'UTF-8')`
    - Compute `$safe_js` via `json_encode($url)`
    - Output the complete Intermediate_Page HTML (see task 3) then call `die()`
    - _Requirements: 2.1, 2.2, 2.3, 5.1, 5.2, 5.3_

  - [ ]* 2.2 Write unit tests for `reotracking_intercept` input validation
    - Test that an empty `$url` triggers a 400 response and halts execution
    - Test that a non-string `$url` triggers a 400 response and halts execution
    - _Requirements: 5.3_

- [ ] 3. Implement the Intermediate_Page HTML output
  - [ ] 3.1 Output valid HTML5 document with required meta tags and Reo + redirect script
    - Include `<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">`
    - Include `<meta http-equiv="refresh" content="5;url={$safe_html}">` as no-JS fallback
    - Embed the combined Reo loader + redirect script using `$safe_js` for the JS `dest` variable and `$safe_html` for the meta refresh URL
    - Set HTTP response status to 200 (default; no explicit `Location` header)
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 4.1, 4.2, 4.3, 4.4_

  - [ ]* 3.2 Write property test for URL escaping — HTML context
    - **Property 1: `htmlspecialchars()` output never contains unescaped `<`, `>`, `"`, `'`, or `&`**
    - **Validates: Requirements 5.1, 4.4**

  - [ ]* 3.3 Write property test for URL escaping — JS context
    - **Property 2: `json_encode()` output is always a valid JSON string literal that round-trips to the original URL**
    - **Validates: Requirements 5.2**

- [ ] 4. Checkpoint — Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 5. Integration and wiring verification
  - [ ] 5.1 Verify hook suppresses default YOURLS redirect
    - Confirm `die()` is called after HTML output so YOURLS never sends a `Location` header
    - Confirm the plugin file loads without PHP errors when YOURLS bootstraps
    - _Requirements: 2.3, 1.3_

  - [ ]* 5.2 Write integration test for full redirect intercept flow
    - Simulate a YOURLS `redirect_shorturl` action firing with a valid URL and assert the output contains the Reo script and the escaped Target_URL
    - Simulate the action with an invalid URL and assert a 400 response
    - _Requirements: 2.1, 2.2, 2.3, 3.1, 5.3_

- [ ] 6. Final checkpoint — Ensure all tests pass
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional and can be skipped for a faster MVP
- The plugin has no database changes, no admin UI, and no external dependencies beyond the YOURLS plugin API — deactivation automatically restores default behaviour (Requirement 6.1, 6.2)
- All requirements are covered by tasks 1–5
