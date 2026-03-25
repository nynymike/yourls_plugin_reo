# Requirements Document

## Introduction

The `yourls-reo-tracking` plugin extends YOURLS to inject Reo.dev analytics tracking on every short URL redirect. Instead of immediately sending the visitor to the target URL, the plugin serves a lightweight intermediate HTML page that loads the Reo.js tracking snippet and then automatically forwards the visitor to the original destination. This enables Reo.dev visitor intelligence to capture identity and behavioral data before the redirect completes.

## Glossary

- **Plugin**: The `yourls-reo-tracking` PHP plugin installed in the YOURLS `/user/plugins/yourls-reo-tracking/` directory.
- **YOURLS**: The self-hosted URL shortening application that manages short URLs and their redirect targets.
- **Short_URL**: A shortened URL managed by YOURLS that maps to a target destination URL.
- **Target_URL**: The original destination URL that a Short_URL resolves to.
- **Intermediate_Page**: The HTML page served by the Plugin in place of an immediate HTTP redirect.
- **Reo_Snippet**: The Reo.dev JavaScript tracking code that initialises the Reo client with `clientID: "d879136bc2a2e75"`.
- **Visitor**: The end user who clicks or follows a Short_URL.
- **Hook**: A YOURLS action or filter registered via `yourls_add_action()` or `yourls_add_filter()`.

## Requirements

### Requirement 1: Plugin Registration

**User Story:** As a YOURLS administrator, I want to install the plugin from the plugins directory, so that I can activate it through the YOURLS admin interface without modifying core files.

#### Acceptance Criteria

1. THE Plugin SHALL include a plugin header comment block containing `Plugin Name`, `Plugin URI`, `Description`, `Version`, `Author`, and `Author URI` fields recognised by YOURLS.
2. THE Plugin SHALL register all hooks inside the main plugin PHP file using `yourls_add_action()` or `yourls_add_filter()`.
3. WHEN the Plugin is activated in the YOURLS admin panel, THE Plugin SHALL register its redirect hook without producing PHP errors or warnings.

---

### Requirement 2: Intercept Redirect

**User Story:** As a YOURLS administrator, I want every short URL redirect to pass through the Plugin, so that tracking fires for all visitors regardless of which short URL they use.

#### Acceptance Criteria

1. WHEN YOURLS resolves a Short_URL to a Target_URL, THE Plugin SHALL intercept the redirect before any HTTP `Location` header is sent to the Visitor.
2. THE Plugin SHALL hook into the YOURLS redirect mechanism using the `redirect_shorturl` action or the earliest available pre-redirect hook that provides the Target_URL.
3. WHEN the Plugin intercepts a redirect, THE Plugin SHALL suppress the default YOURLS redirect response.

---

### Requirement 3: Serve Intermediate Page

**User Story:** As a YOURLS administrator, I want visitors to see an intermediate page that loads the Reo tracking script, so that Reo.dev can identify and record the visitor before they reach the destination.

#### Acceptance Criteria

1. WHEN the Plugin intercepts a redirect, THE Plugin SHALL output a complete, valid HTML5 document as the HTTP response.
2. THE Intermediate_Page SHALL include the Reo_Snippet verbatim in the `<head>` element:
   ```html
   <script type="text/javascript">!function(){var e,t,n;e="d879136bc2a2e75",t=function(){Reo.init({clientID:"d879136bc2a2e75"})},(n=document.createElement("script")).src="https://static.reo.dev/"+e+"/reo.js",n.defer=!0,n.onload=t,document.head.appendChild(n)}();</script>
   ```
3. THE Intermediate_Page SHALL set the HTTP response status code to `200 OK`.
4. THE Intermediate_Page SHALL include a `<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">` tag.

---

### Requirement 4: Automatic Forward to Target URL

**User Story:** As a visitor, I want to be automatically forwarded to the destination after the tracking script fires, so that my browsing experience is not interrupted.

#### Acceptance Criteria

1. WHEN the Reo_Snippet has finished loading (`onload` callback fires), THE Intermediate_Page SHALL navigate the Visitor's browser to the Target_URL.
2. THE Intermediate_Page SHALL include a JavaScript `window.location` redirect that executes after the Reo_Snippet `onload` callback.
3. IF JavaScript is disabled in the Visitor's browser, THEN THE Intermediate_Page SHALL forward the Visitor to the Target_URL via a `<meta http-equiv="refresh">` fallback tag.
4. THE Intermediate_Page SHALL embed the Target_URL in a way that prevents XSS by HTML-encoding the URL before rendering it into the page.

---

### Requirement 5: Target URL Safety

**User Story:** As a YOURLS administrator, I want the plugin to safely handle the target URL, so that malicious short URL targets cannot inject code into the intermediate page.

#### Acceptance Criteria

1. WHEN the Plugin renders the Target_URL into the Intermediate_Page HTML, THE Plugin SHALL escape the Target_URL using `htmlspecialchars()` with `ENT_QUOTES` before insertion.
2. WHEN the Plugin renders the Target_URL into a JavaScript string literal, THE Plugin SHALL JSON-encode the Target_URL using `json_encode()` before insertion.
3. IF the Target_URL is empty or not a string, THEN THE Plugin SHALL terminate execution and return a `400 Bad Request` HTTP response.

---

### Requirement 6: Plugin Deactivation

**User Story:** As a YOURLS administrator, I want to deactivate the plugin and restore normal redirect behaviour, so that I can disable tracking without uninstalling YOURLS.

#### Acceptance Criteria

1. WHEN the Plugin is deactivated in the YOURLS admin panel, THE Plugin SHALL cease intercepting redirects and YOURLS SHALL resume its default redirect behaviour.
2. THE Plugin SHALL not modify any YOURLS database tables or core configuration files during activation or deactivation.
