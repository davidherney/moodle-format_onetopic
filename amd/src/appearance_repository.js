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
 * Module to handle appearance AJAX requests.
 *
 * @module     format_onetopic/appearance_repository
 * @copyright  2026 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';

/**
 * Delete an appearance.
 *
 * @param {Number} id The appearance id.
 * @return {Promise}
 */
export const deleteAppearance = (id) => {
    const request = {
        methodname: 'format_onetopic_delete_appearance',
        args: {id: Number(id)},
    };
    return Ajax.call([request])[0];
};

/**
 * Get appearance usages.
 *
 * @param {String} uniquecode The appearance unique code.
 * @return {Promise}
 */
export const getAppearanceUsages = (uniquecode) => {
    const request = {
        methodname: 'format_onetopic_get_appearance_usages',
        args: {uniquecode},
    };
    return Ajax.call([request])[0];
};

/**
 * Check if a unique code is available.
 *
 * @param {String} uniquecode The unique code to check.
 * @return {Promise}
 */
export const checkUniquecode = (uniquecode) => {
    const request = {
        methodname: 'format_onetopic_check_uniquecode',
        args: {uniquecode},
    };
    return Ajax.call([request])[0];
};
