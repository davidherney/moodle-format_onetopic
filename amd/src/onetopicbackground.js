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
 * Onetopic background selector.
 *
 * @module    format_onetopic/onetopicbackground
 * @copyright 2021 David Herney Bernal - cirano
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import $ from 'jquery';
import ModalFactory from 'core/modal_factory';
import ModalSaveCancel from 'core/modal_save_cancel';
import ModalEvents from 'core/modal_events';

/**
 * Component initialization.
 *
 * @method init
 * @param {String} controlid The control id.
 */
export const init = (controlid) => {

    var $controlinput = $('#' + controlid);
    var $control = $controlinput.parent('.backgroundpicker');
    var $controlwindow = $control.find('.backgroundpickerwindow');
    var $controlbutton = $control.find('.backgroundpickerselector');
    var $colorpickerinput = $controlwindow.find('input.form-control[type="text"]');

    // Initialize the modal window.
    var title = $controlwindow.attr('title');
    var buttons = [];
    buttons.save = $controlwindow.data('savelabel');

    var setEvents = function(modal) {
        modal.getRoot().on(ModalEvents.cancel, () => {
            modal.hide();
        });

        modal.getRoot().on(ModalEvents.save, () => {
            var newcolor = $colorpickerinput.val();
            var val = $controlinput.val();
            var color = readColor(val);
            if (color) {
                val = val.replace(color, newcolor);
            } else if (val.trim() === '') {
                val = newcolor;
            } else {
                val += ' ' + newcolor;
            }
            $controlinput.val(val);
            modal.hide();
        });
    };

    ModalFactory.create({
        'title': title,
        'body': $controlwindow,
        'type': ModalSaveCancel.TYPE,
        'large': true,
        'buttons': buttons
    })
    .done(function(modal) {
        var $modalBody = modal.getBody();
        $modalBody.append($controlwindow);
        $control.data('modal', modal);
        setEvents(modal);
    });

    // Color picker control.
    $controlbutton.on('click', function(e) {
        e.preventDefault();
        $controlwindow.removeClass('hidden');
        var currentval = $controlinput.val();

        var color = readColor(currentval);

        $colorpickerinput.val(color);
        $control.data('modal').show();
    });

};

/**
 * Extracts the first color from a text string. Recognizes hexadecimal, rgba and hsla.
 *
 * @param {string} text
 * @returns
 */
var readColor = function(text) {
    var regexcolor = new RegExp(
        /#[0-9a-fA-F]{6}/.source
        + /|#[0-9a-fA-F]{3}/.source
        + /|rgba?\(\s*([0-9]{1,3}\s*,\s*){2}[0-9]{1,3}\s*(,\s*[0-9.]+\s*)?\)/.source
        + /|hsla?\(\s*([0-9]{1,3}\s*,\s*){2}[0-9]{1,3}\s*(,\s*[0-9.]+\s*)?\)/.source);

    var color = text.match(regexcolor);
    if (color) {
        color = color[0];
    }

    return color;
};
