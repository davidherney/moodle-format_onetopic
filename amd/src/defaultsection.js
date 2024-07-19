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

/*
 * @package   format_onetopic
 * @copyright 2021 David Herney Bernal - cirano
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Default section initialization.
 *
 * This will click on tab 1 to reveal its content on loading the page when the following criteria are met:
 * - section 0 is rendered above the tabs
 * - the page is called without a specific section id in the URL
 *
 * @method init
 */
export const init = () => {
    // Do this only if section 0 is before the tabs and no section is already set in the URL.
    const generalSection = document.querySelector('.general-section');
    var currentUrl = new URL(window.location.href);
    var params = new URLSearchParams(currentUrl.search);
    if (generalSection && !params.has('section')) {
        if (params.has('id')) {
            const idValue = params.get('id');
            if (idValue) {
                var link = '';
                // If a section is marked show this by default.
                const marked = document.querySelector('.marker .nav-link');
                if (marked) {
                    const url = marked.getAttribute('href');
                    link = url.split('#')[0]; // Keep only the URL w/o any anchors.
                } else { // Show the 1st section.
                    link = getBaseURL() + '/course/view.php?id=' + idValue + '&section=1';
                }
                window.location.href = link;
            }
        }
    }
};

/**
 * Get the base URL of the current page.
 */
function getBaseURL() {
    // Get the protocol
    var protocol = window.location.protocol;
    // Get the hostname
    var hostname = window.location.hostname;
    // Get the port, if specified
    var port = window.location.port;

    // Construct the base URL
    var baseURL = protocol + "//" + hostname;
    if (port) {
        baseURL += ":" + port;
    }
    return baseURL;
}
