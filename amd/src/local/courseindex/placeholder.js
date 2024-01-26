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
 * Course index placeholder replacer.
 *
 * @module     format_onetopic/local/courseindex/placeholder
 * @class      format_onetopic/local/courseindex/placeholder
 * @copyright  2023 Friederike Schwager, RWTH Aachen University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {BaseComponent} from 'core/reactive';
import Templates from 'core/templates';
import {getCurrentCourseEditor} from 'core_courseformat/courseeditor';
import Pending from 'core/pending';

/**
 * Course index placeholder replacer.
 *
 * @module     format_onetopic/local/courseindex/placeholder
 * @copyright  2022 Marina Glancy
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
export default class Component extends BaseComponent {

    /**
     * Static method to create a component instance form the mustache template.
     *
     * @param {element|string} target the DOM main element or its ID
     * @param {object} selectors optional css selector overrides
     * @return {Component}
     */
    static init(target, selectors) {
        return new Component({
            element: document.getElementById(target),
            reactive: getCurrentCourseEditor(),
            selectors,
        });
    }

    /**
     * Component creation hook.
     */
    create() {
        // Add a pending operation waiting for the initial content.
        this.pendingContent = new Pending(`core_courseformat/placeholder:loadcourseindex`);
    }

    /**
     * Initial state ready method.
     *
     * This stateReady to be async because it loads the real courseindex.
     *
     * @param {object} state the initial state
     */
    async stateReady(state) {

        // Check if we have a static course index already loded from a previous page.
        if (!this.loadStaticContent()) {
            await this.loadTemplateContent(state);
        }
    }

    /**
     * Load the course index from the session storage if any.
     *
     * @return {boolean} true if the static version is loaded form the session
     */
    loadStaticContent() {
        // Load the previous static course index from the session cache.
        const index = this.reactive.getStorageValue(`courseIndex`);
        if (index.html && index.js) {
            Templates.replaceNode(this.element, index.html, index.js);
            this.pendingContent.resolve();
            return true;
        }
        return false;
    }

    /**
     * Load the course index template.
     *
     * @param {Object} state the initial state
     */
    async loadTemplateContent(state) {
        // Collect section information from the state.
        const exporter = this.reactive.getExporter();
        const data = exporter.course(state);
        try {
            // To render an HTML into our component we just use the regular Templates module.
            const {html, js} = await Templates.renderForPromise(
                'format_onetopic/local/courseindex/courseindex',
                data,
            );
            Templates.replaceNode(this.element, html, js);
            this.pendingContent.resolve();

            // Save the rendered template into the session cache.
            this.reactive.setStorageValue(`courseIndex`, {html, js});
        } catch (error) {
            this.pendingContent.resolve(error);
            throw error;
        }
    }
}
