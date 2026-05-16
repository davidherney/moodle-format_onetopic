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
 * Module to handle the editappearance form interactions.
 *
 * Auto-suggests a unique code from the name field and validates its availability via AJAX.
 *
 * @module     format_onetopic/editappearance
 * @copyright  2026 David Herney @ BambuCo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import Ajax from 'core/ajax';
import {get_strings as getStrings} from 'core/str';

const DEBOUNCE_MS = 500;

let debounceTimer = null;
let uniquecodeAvailable = false;

/** @type {Object<string, string>} Preloaded lang strings. */
let strings = {};

/**
 * Convert a name string into a valid unique code.
 *
 * Rules: lowercase, spaces replaced with _, remove special characters (only a-z0-9_- allowed).
 *
 * @param {string} name The name to convert.
 * @returns {string} The sanitized unique code.
 */
const nameToCode = (name) => {
    return name
        .toLowerCase()
        .trim()
        .replace(/\s+/g, '_')
        .replace(/[^a-z0-9_-]/g, '');
};

/**
 * Check uniquecode availability via AJAX.
 *
 * @param {string} uniquecode The code to check.
 * @returns {Promise<boolean>} Whether the code is available.
 */
const checkUniquecode = (uniquecode) => {
    const request = {
        methodname: 'format_onetopic_check_uniquecode',
        args: {uniquecode},
    };
    return Ajax.call([request])[0].then((response) => response.available);
};

/**
 * Update the validation UI for the uniquecode field.
 *
 * @param {HTMLElement} field The uniquecode input element.
 * @param {string} status One of 'checking', 'available', 'unavailable', 'empty'.
 * @param {HTMLElement} feedback The feedback container element.
 * @param {HTMLElement} submitBtn The submit button.
 */
const updateValidationUI = (field, status, feedback, submitBtn) => {

    field.classList.remove('is-valid', 'is-invalid');
    feedback.innerHTML = '';
    feedback.classList.remove('text-success', 'text-danger', 'text-muted');

    switch (status) {
        case 'checking':
            feedback.classList.add('text-muted');
            feedback.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i>' + strings.checkingavailability;
            submitBtn.disabled = true;
            uniquecodeAvailable = false;
            break;
        case 'available':
            field.classList.add('is-valid');
            feedback.classList.add('text-success');
            feedback.innerHTML = '<i class="fa fa-check mr-1"></i>' + strings.uniquecodeavailable;
            submitBtn.disabled = false;
            uniquecodeAvailable = true;
            break;
        case 'unavailable':
            field.classList.add('is-invalid');
            feedback.classList.add('text-danger');
            feedback.innerHTML = '<i class="fa fa-times mr-1"></i>' + strings.uniquecodeexists;
            submitBtn.disabled = true;
            uniquecodeAvailable = false;
            break;
        case 'empty':
            submitBtn.disabled = true;
            uniquecodeAvailable = false;
            break;
    }
};

/**
 * Initialise the edit appearance form interactions.
 */
export const init = async() => {
    const nameField = document.getElementById('id_name');
    const uniquecodeField = document.getElementById('id_uniquecode');
    const submitBtn = document.getElementById('id_submitbutton');

    if (!nameField || !uniquecodeField || !submitBtn) {
        return;
    }

    // If uniquecode is frozen (editing mode), do nothing.
    if (uniquecodeField.disabled || uniquecodeField.readOnly ||
        uniquecodeField.closest('.felement')?.querySelector('input[type="hidden"][name="uniquecode"]')) {
        return;
    }

    // Preload all needed lang strings in one request.
    const stringKeys = [
        {key: 'checkingavailability', component: 'format_onetopic'},
        {key: 'uniquecodeavailable', component: 'format_onetopic'},
        {key: 'uniquecodeexists', component: 'format_onetopic'},
    ];
    const fetched = await getStrings(stringKeys);
    strings = {
        checkingavailability: fetched[0],
        uniquecodeavailable: fetched[1],
        uniquecodeexists: fetched[2],
    };

    // Create feedback element below uniquecode field.
    const feedback = document.createElement('div');
    feedback.className = 'form-text mt-1';
    feedback.id = 'uniquecode-feedback';
    uniquecodeField.parentNode.appendChild(feedback);

    // Initially disable submit.
    submitBtn.disabled = true;

    // Track if user has manually edited the uniquecode.
    let userEditedCode = false;

    /**
     * Validate the current uniquecode with debounce.
     */
    const scheduleValidation = () => {
        const code = uniquecodeField.value.trim();
        if (debounceTimer) {
            clearTimeout(debounceTimer);
        }

        if (!code) {
            updateValidationUI(uniquecodeField, 'empty', feedback, submitBtn);
            return;
        }

        updateValidationUI(uniquecodeField, 'checking', feedback, submitBtn);

        debounceTimer = setTimeout(async() => {
            try {
                const available = await checkUniquecode(code);
                // Only update if the field value hasn't changed in the meantime.
                if (uniquecodeField.value.trim() === code) {
                    updateValidationUI(uniquecodeField, available ? 'available' : 'unavailable', feedback, submitBtn);
                }
            } catch (e) {
                uniquecodeAvailable = false;
            }
        }, DEBOUNCE_MS);
    };

    // Auto-suggest uniquecode from name.
    nameField.addEventListener('input', () => {
        if (!userEditedCode) {
            const code = nameToCode(nameField.value);
            uniquecodeField.value = code;
            scheduleValidation();
        }
    });

    // When user edits uniquecode directly, stop auto-suggesting.
    uniquecodeField.addEventListener('input', () => {
        userEditedCode = true;
        // Sanitize inline.
        uniquecodeField.value = uniquecodeField.value.toLowerCase().replace(/[^a-z0-9_-]/g, '');
        scheduleValidation();
    });

    // Allow re-enabling auto-suggestion if user clears the uniquecode field.
    uniquecodeField.addEventListener('change', () => {
        if (!uniquecodeField.value.trim()) {
            userEditedCode = false;
        }
    });

    // Prevent form submission if uniquecode is not available (except for cancel).
    const form = uniquecodeField.closest('form');
    if (form) {
        const cancelBtn = document.getElementById('id_cancel');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => {
                uniquecodeAvailable = true;
            });
        }
        form.addEventListener('submit', (e) => {
            if (!uniquecodeAvailable) {
                e.preventDefault();
            }
        });
    }
};
