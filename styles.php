<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Display the general CSS style to tabs based on the site-level default appearance.
 *
 * @package   format_onetopic
 * @copyright 2023 David Herney Bernal - cirano
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_MOODLE_COOKIES', true);

// Require_login is not needed here.
// phpcs:disable moodle.Files.RequireLogin.Missing
require_once('../../../config.php');

$revision = optional_param('revision', 0, PARAM_ALPHANUM);

$csstabstyles = '';

// Site-level sections appearance (tab styles).
$uniquecode = get_config('format_onetopic', 'defaultsectionsappearance');
if (!empty($uniquecode)) {
    $styles = \format_onetopic\local\appearances::get_styles_by_uniquecode($uniquecode);

    if ($styles) {
        $csstabstyles .= \format_onetopic\local\appearances::generate_generic_css($styles);
    }
}

// Site-level subsections appearance (mod_subsection styles).
$subuniquecode = get_config('format_onetopic', 'defaultsubsectionsappearance');
if (!empty($subuniquecode)) {
    $substyles = \format_onetopic\local\appearances::get_styles_by_uniquecode($subuniquecode);

    if ($substyles) {
        $csstabstyles .= \format_onetopic\local\appearances::generate_subsection_generic_css($substyles);
    }
}

$csstabstyles = trim($csstabstyles);
$etag = md5($csstabstyles . $revision);

// ETag validation: Return 304 if client has the current version. This will
// preserve the existing cache for $cachelifetime.
if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
    $clientetag = trim($_SERVER['HTTP_IF_NONE_MATCH'], '"');
    if ($clientetag === $etag) {
        header('HTTP/1.1 304 Not Modified');
        header('ETag: "' . $etag . '"');
        exit;
    }
}

// Return empty response for edge cases (no styles configured & direct URL access).
if (empty($csstabstyles)) {
    header('HTTP/1.1 304 Not Modified');
    header('Content-Length: 0');
    exit;
}

// Cache for 1 year, this is safe due to cache busting via revision param.
$cachelifetime = 31536000;
header('Cache-Control: public, max-age=' . $cachelifetime . ', immutable');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cachelifetime) . ' GMT');
header('ETag: "' . $etag . '"');

// Content headers.
header('Content-Length: ' . strlen($csstabstyles));
header('Content-Disposition: inline; filename="styles.php"');
header('Content-Type: text/css; charset=utf-8');
header('X-Content-Type-Options: nosniff');

echo $csstabstyles;
