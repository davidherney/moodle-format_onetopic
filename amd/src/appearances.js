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
 * Appearances actions.
 *
 * @module     format_onetopic/appearances
 * @copyright  2026 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {dispatchEvent} from 'core/event_dispatcher';
import Modal from 'core/modal';
import Notification from 'core/notification';
import Pending from 'core/pending';
import {prefetchStrings} from 'core/prefetch';
import {getString} from 'core/str';
import {add as addToast} from 'core/toast';
import Templates from 'core/templates';
import {deleteAppearance, getAppearanceUsages} from 'format_onetopic/appearance_repository';
import * as reportEvents from 'core_reportbuilder/local/events';
import * as reportSelectors from 'core_reportbuilder/local/selectors';

const SELECTORS = {
    DELETE: '[data-action="appearance-delete"]',
    USAGES: '[data-action="appearance-usages"]',
};

/**
 * Initialise module.
 */
export const init = () => {
    prefetchStrings('format_onetopic', [
        'deleteappearance',
        'deleteappearanceconfirm',
        'appearancedeleted',
        'appearanceusages',
        'nousagesfound',
    ]);

    prefetchStrings('core', [
        'delete',
    ]);

    registerEventListeners();
};

/**
 * Register event listeners.
 */
const registerEventListeners = () => {
    document.addEventListener('click', event => {

        // Delete single appearance.
        const deleteBtn = event.target.closest(SELECTORS.DELETE);
        if (deleteBtn) {
            event.preventDefault();

            const {appearanceId, appearanceName} = deleteBtn.dataset;

            Notification.saveCancelPromise(
                getString('deleteappearance', 'format_onetopic'),
                getString('deleteappearanceconfirm', 'format_onetopic', appearanceName),
                getString('delete', 'core'),
                {triggerElement: deleteBtn}
            ).then(() => {
                const pendingPromise = new Pending('format_onetopic/appearance:delete');
                const reportElement = event.target.closest(reportSelectors.regions.report);

                // eslint-disable-next-line promise/no-nesting
                return deleteAppearance(appearanceId)
                    .then(() => addToast(getString('appearancedeleted', 'format_onetopic')))
                    .then(() => {
                        dispatchEvent(reportEvents.tableReload, {preservePagination: true}, reportElement);
                        return pendingPromise.resolve();
                    })
                    .catch(Notification.exception);
            }).catch(() => {
                return;
            });
        }

        // Show usages modal.
        const usagesBtn = event.target.closest(SELECTORS.USAGES);
        if (usagesBtn) {
            event.preventDefault();

            const {appearanceUniquecode, appearanceName} = usagesBtn.dataset;

            const pendingPromise = new Pending('format_onetopic/appearance:usages');

            getAppearanceUsages(appearanceUniquecode)
                .then(response => {
                    return Modal.create({
                        removeOnClose: true,
                        large: true,
                    }).then(modal => {
                        const body = Templates.render('format_onetopic/appearance_usages', {
                            usages: response.usages,
                            hasusages: response.usages.length > 0,
                        });
                        modal.setTitle(getString('appearanceusages', 'format_onetopic', appearanceName));
                        modal.setBody(body);
                        modal.show();
                        return pendingPromise.resolve();
                    });
                })
                .catch(Notification.exception);
        }
    });
};
