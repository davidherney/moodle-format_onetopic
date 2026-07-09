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
 * Hide long descriptions and availability in inline layout and show them via modal icons.
 *
 * Content is kept in the DOM (hidden by CSS) and read on demand when the modal opens.
 *
 * @module     format_onetopic/inlinetruncate
 * @copyright  2025 David Herney Bernal - cirano. https://bambuco.co
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import ModalFactory from 'core/modal_factory';
import {get_string as getString} from 'core/str';

const SELECTORS = {
    INLINE_LAYOUT: '.resourcelayout_inline',
    DESCRIPTION: '.activity-altcontent',
    AVAILABILITY: '.activity-availability',
    MODAL_ICON: '.onetopic-inline-modal-icon',
};

let initialized = false;
let strDescription = '';
let strRestrictions = '';

/**
 * Create a clickable icon that opens a modal reading content from source elements.
 *
 * @param {string} iconClass FontAwesome icon class.
 * @param {string} title Modal title.
 * @param {HTMLElement[]} sourceElements DOM elements whose innerHTML will be read on click.
 * @returns {HTMLElement} The icon button element.
 */
const createModalIcon = (iconClass, title, sourceElements) => {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'onetopic-inline-modal-icon btn btn-link';
    btn.title = title;
    btn.innerHTML = '<i class="icon fa ' + iconClass + ' fa-fw" aria-hidden="true"></i>';
    btn.dataset.modalTitle = title;
    btn._sourceElements = sourceElements;
    return btn;
};

/**
 * Process a single activity item: add modal icons for description/availability.
 *
 * @param {HTMLElement} activityItem The .activity-item element.
 */
const processActivity = (activityItem) => {
    if (activityItem.dataset.inlineProcessed) {
        return;
    }
    activityItem.dataset.inlineProcessed = '1';

    const description = activityItem.querySelector(SELECTORS.DESCRIPTION);
    const availabilities = activityItem.querySelectorAll(SELECTORS.AVAILABILITY);

    if (!description && availabilities.length === 0) {
        return;
    }

    // Find the activity name area to place icons next to it.
    const nameArea = activityItem.querySelector('.activity-name-area');
    if (!nameArea) {
        return;
    }

    const iconContainer = document.createElement('span');
    iconContainer.className = 'onetopic-inline-icons ms-1';

    if (description) {
        iconContainer.appendChild(createModalIcon('fa-info-circle', strDescription, [description]));
    }

    if (availabilities.length > 0) {
        iconContainer.appendChild(createModalIcon('fa-lock', strRestrictions, Array.from(availabilities)));
    }

    activityItem.appendChild(iconContainer);
};

/**
 * Process all activity items inside inline layouts.
 */
const processAll = () => {
    const containers = document.querySelectorAll(SELECTORS.INLINE_LAYOUT);
    containers.forEach((container) => {
        const items = container.querySelectorAll('.activity-item');
        items.forEach((item) => {
            processActivity(item);
        });
    });
};

/**
 * Initialize the inline truncate module.
 */
export const init = async() => {
    if (initialized) {
        return;
    }
    initialized = true;

    [strDescription, strRestrictions] = await Promise.all([
        getString('description'),
        getString('accessrestrictions', 'availability'),
    ]);

    // Process existing elements.
    processAll();

    // Listen for click events on modal icons (event delegation).
    document.addEventListener('click', (e) => {
        const btn = e.target.closest(SELECTORS.MODAL_ICON);
        if (!btn) {
            return;
        }

        e.preventDefault();

        // Reuse cached modal if available.
        if (btn._modal) {
            btn._modal.show();
            return;
        }

        // Read content from source elements at the moment of opening.
        let bodyHtml = '';
        btn._sourceElements.forEach((el) => {
            bodyHtml += el.innerHTML;
        });

        ModalFactory.create({
            title: btn.dataset.modalTitle,
            body: bodyHtml,
        }).then((modal) => {
            btn._modal = modal;
            modal.show();
            return modal;
        }).catch();
    });

    // Observe DOM changes for dynamically added content.
    const observer = new MutationObserver(() => {
        processAll();
    });
    const courseContent = document.querySelector('.format-onetopic .onetopic');
    if (courseContent) {
        observer.observe(courseContent, {childList: true, subtree: true});
    }
};
