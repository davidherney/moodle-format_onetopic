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
 * Display the general CSS style to tabs.
 *
 * @package   format_onetopic
 * @copyright 2023 David Herney Bernal - cirano
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_MOODLE_COOKIES', true);

// Require_login is not needed here.
// phpcs:disable moodle.Files.RequireLogin.Missing
require_once('../../../config.php');

@header('Content-Disposition: inline; filename="styles.php"');
@header('Content-Type: text/css; charset=utf-8');

$withunits = ['font-size', 'line-height', 'margin', 'padding', 'border-width', 'border-radius'];
$csscontent = '';
$csstabstyles = '';
$tabstyles = get_config('format_onetopic', 'tabstyles');
if (!empty($tabstyles)) {
    $tabstyles = @json_decode($tabstyles);

    if (is_object($tabstyles)) {

        $precedence = ['default', 'childs', 'childindex', 'active', 'parent', 'highlighted', 'disabled', 'hover'];

        $orderedtabs = new \stdClass();
        foreach ($precedence as $type) {
            if (property_exists($tabstyles, $type)) {
                $orderedtabs->$type = $tabstyles->$type;
            }
        }

        foreach ($orderedtabs as $type => $styles) {

            $important = false;
            switch ($type) {
                case 'active':
                    $csscontent .= '#tabs-tree-start .verticaltabs .format_onetopic-tabs .nav-item a.nav-link.active, ';
                    $csscontent .= '#tabs-tree-start .nav-tabs a.nav-link.active, ';
                    $csscontent .= '#tabs-tree-start .onetopic-tab-body .nav-tabs a.nav-link.active, ';
                    $csscontent .= '#tabs-tree-start .onetopic-tab-body .nav-tabs .nav-item.subtopic a.nav-link.active';
                    $important = true;
                break;
                case 'parent':
                    $csscontent .= '#tabs-tree-start .verticaltabs .format_onetopic-tabs .nav-item.haschilds a.nav-link, ';
                    $csscontent .= '#tabs-tree-start .nav-tabs .nav-item.haschilds a.nav-link';
                break;
                case 'highlighted':
                    $csscontent .= '#tabs-tree-start .verticaltabs .format_onetopic-tabs .nav-item.marker a.nav-link, ';
                    $csscontent .= '#tabs-tree-start .nav-tabs .nav-item.marker a.nav-link, ';
                    $csscontent .= '#tabs-tree-start .onetopic-tab-body .nav-tabs .nav-item.marker a.nav-link';
                    $csscontent .= '#tabs-tree-start .onetopic-tab-body .nav-tabs .nav-item.subtopic.marker a.nav-link';
                    $important = true;
                break;
                case 'disabled':
                    $csscontent .= '#tabs-tree-start .verticaltabs .format_onetopic-tabs .nav-item.disabled a.nav-link, ';
                    $csscontent .= '#tabs-tree-start .nav-tabs .nav-item.disabled a.nav-link, ';
                    $csscontent .= '#tabs-tree-start .onetopic-tab-body .nav-tabs .nav-item.disabled a.nav-link';
                    $csscontent .= '#tabs-tree-start .onetopic-tab-body .nav-tabs .nav-item.subtopic.disabled a.nav-link';
                    $important = true;
                break;
                case 'hover':
                    $csscontent .= '#tabs-tree-start .verticaltabs .format_onetopic-tabs .nav-item a.nav-link:hover, ';
                    $csscontent .= '#tabs-tree-start .format_onetopic-tabs.nav-tabs .nav-item a.nav-link:hover, ';
                    $csscontent .= '#tabs-tree-start .onetopic-tab-body .format_onetopic-tabs.nav-tabs' .
                                    ' .nav-item a.nav-link:hover';
                break;
                case 'childs':
                    $csscontent .= '#tabs-tree-start .onetopic-tab-body .nav-tabs .nav-item.subtopic a.nav-link';
                break;
                case 'childindex':
                    $csscontent .= '#tabs-tree-start .onetopic-tab-body .nav-tabs' .
                                    ' .nav-item.subtopic.tab_initial a.nav-link';
                break;
                default:
                    $csscontent .= '#tabs-tree-start .verticaltabs .format_onetopic-tabs .nav-item a.nav-link, ';
                    $csscontent .= '#tabs-tree-start .nav-tabs a.nav-link';
            }

            $csscontent .= '{';
            $units = [];

            // Check if exist units for some rules.
            foreach ($styles as $key => $value) {

                // Check if the key start with the units prefix.
                if (strpos($key, 'unit-') === 0) {

                    // Remove the prefix.
                    $ownerkey = str_replace('unit-', '', $key);
                    $units[$ownerkey] = $value;
                    unset($styles->$key);
                }
            }

            foreach ($styles as $key => $value) {

                // If exist a unit for the rule, apply it.
                if (isset($units[$key])) {
                    $value = $value . $units[$key];
                } else if (in_array($key, $withunits)) {
                    // If the rule need units, apply px by default.
                    $value = $value . 'px';
                }

                if ($key == 'others') {
                    $csscontent .= $value . ';';
                } else {
                    $csscontent .= $key . ':' . $value . ($important ? '!important' : '') . ';';
                }
            }

            $csscontent .= '} ';
        }

        // Clean the CSS for html tags.
        $csstabstyles = preg_replace('/<[^>]*>/', '', $csscontent);
    }
}

echo $csstabstyles;
