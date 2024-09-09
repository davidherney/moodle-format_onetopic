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
    const currentUrl = new URL(window.location.href);
    let nextUrl = new URL(window.location.href);
    var params = new URLSearchParams(currentUrl.search);
    const idValue = params.get('id');
    const sectionValue = params.get('section');
    if (generalSection && idValue && !sectionValue) {
        nextUrl.search = currentUrl.search + "&section=1";
        var link = nextUrl.href;
        var sectionId = 1;
        // If a section is marked show this by default. This won't exist if tabs are hidden
        const marked = document.querySelector('.marker .nav-link');
        if (marked) {
            const url = marked.getAttribute('href');
            link = url.split('#')[0]; // Keep only the URL w/o any anchors.
            sectionId = link.split('section=')[1] ?? 1; // Get the section ID from the URL.
        }

        let tabsVisible = document.getElementById("tabs-tree-start").classList.contains("hastopictabs");
        let contentToReplace = "#tabs-tree-start .onetopic-tab-body";
        if(tabsVisible==false) {
            contentToReplace = "#tabs-tree-start>div[role='main']";
        }
        let xhrSection1 = new XMLHttpRequest();
        xhrSection1.responseType = "document";
        xhrSection1.onload = () => {
            let doc = xhrSection1.response;
            let tabBody = document.querySelector(contentToReplace);
            tabBody.textContent = '';
            tabBody.insertAdjacentHTML("beforeend",
                doc.querySelector(contentToReplace).innerHTML);
            if(tabsVisible) {
                document.querySelector("a.nav-link[href*='section=" + sectionId + "#tabs-tree-start']").classList.add("active");
            }
            history.replaceState({}, "", xhrSection1.responseURL);
        };
        xhrSection1.open("GET", link);
        xhrSection1.send();
    }
};
