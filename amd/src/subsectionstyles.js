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
 * Subsection styles editor.
 *
 * @module    format_onetopic/subsectionstyles
 * @copyright 2026 David Herney Bernal - cirano
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import $ from 'jquery';
import ModalFactory from 'core/modal_factory';

var $container = null;
var $styleswindow = null;
var globalstyles = {};
var $inputtosave = null;

/**
 * Component initialization.
 *
 * @method init
 */
export const init = () => {

    $container = $('#onetopic-subsectionstyles');
    $inputtosave = $container.find('textarea.savecontrol');
    $styleswindow = $('#onetopic-subsectionstyles-window');

    if ($inputtosave.val().trim() !== '') {
        try {
            globalstyles = JSON.parse($inputtosave.val());

            if (globalstyles === null) {
                globalstyles = {};
            }
        } catch (e) {
            // eslint-disable-next-line no-console
            console.error(e);
            globalstyles = {};
        }

        applyStyles();
    }

    var $colorpicker = $styleswindow.find('.colorpicker');
    var $setcolor = $colorpicker.find('[data-action="setcolor"]');
    var $colorpickerinput = $colorpicker.find('input');

    // Color picker control.
    $styleswindow.find('[data-control="colorpicker"]').on('click', function() {
        var $node = $(this);

        $colorpicker.show();
        $colorpickerinput.val($node.val());
        $setcolor.data('target', $node);
    }).on('change', function() {
        var $node = $(this);
        var color = $node.val().trim();

        $node.css('background-color', color);
    });

    $setcolor.on('click', function(e) {
        e.preventDefault();

        $colorpicker.hide();
        $setcolor.data('target').val($colorpickerinput.val()).trigger('change');
    });

    $colorpicker.find('[data-action="cancel"]').on('click', function() {
        $colorpicker.hide();
    });

    var title = $styleswindow.data('title');

    // Initialize the modal window.
    ModalFactory.create({
        'title': title,
        'body': ''
    }).done(function(modal) {
        var $modalBody = modal.getBody();
        $styleswindow.show();
        $modalBody.append($styleswindow);
        $styleswindow.data('modal', modal);
    });

    // Cancel the styles.
    $styleswindow.find('[data-action="cancelstyles"]').on('click', function() {
        $styleswindow.data('modal').hide();
    });

    // Save the styles.
    $styleswindow.find('[data-action="setstyles"]').on('click', function() {
        var modal = $styleswindow.data('modal');
        var newstyles = {};

        $styleswindow.find('[data-style]').each(function() {
            var $node = $(this);
            var key = $node.data('style');
            var value = $node.val();

            if (value !== '') {
                newstyles[key] = value;
            }
        });

        globalstyles['default'] = newstyles;
        $inputtosave.val(JSON.stringify(globalstyles));
        applyStyles();

        modal.hide();
    });

    // Open the styles modal for the default type.
    $('#onetopic-subsectionstyles #subsectionstylesetdefault').on('click', function(e) {
        e.preventDefault();
        showStylesWindow();
    });

    // Clear all styles.
    $('#onetopic-subsectionstyles #subsectionstyleclear').on('click', function(e) {
        e.preventDefault();
        globalstyles = {};
        $inputtosave.val('');
        applyStyles();
    });

};

/**
 * Show the styles window with the current default styles.
 */
var showStylesWindow = function() {

    $styleswindow.find('[data-style]').each(function() {
        var $node = $(this);

        if ($node.is('input[type="text"]') || $node.is('input[type="hidden"]')) {
            $node.val('');
            $node.trigger('change');
        } else if ($node.is('select')) {
            $node.find('option').first().prop('selected', true);
        }
    });

    if (globalstyles['default'] !== undefined) {
        var currentstyles = globalstyles['default'];

        Object.entries(currentstyles).forEach(([key, value]) => {
            $styleswindow.find('[data-style="' + key + '"]').val(value);

            // Apply the color.
            $styleswindow.find('[data-control="colorpicker"]').trigger('change');
        });
    }

    $styleswindow.data('modal').show();

};

/**
 * Apply the styles to the subsection preview.
 */
var applyStyles = function() {

    var withunits = ['font-size', 'line-height', 'margin', 'padding', 'border-width', 'border-radius'];
    var csscontent = '';

    if (globalstyles['default'] !== undefined) {
        var styles = globalstyles['default'];

        csscontent += '#onetopic-subsectionstyles .subsection-preview-container {';
        var units = [];
        var stylesarray = Object.entries(styles);

        // Check if exist units for some rules.
        stylesarray.forEach(([key, value]) => {
            if (key.indexOf('unit-') === 0) {
                key = key.replace('unit-', '');
                units[key] = value;
            }
        });

        stylesarray.forEach(([key, value]) => {
            // Exclude units rules.
            if (key.indexOf('unit-') === 0) {
                return;
            }

            // If exist a unit for the rule, apply it.
            if (units[key] !== undefined) {
                value = value + units[key];
            } else if (withunits.indexOf(key) !== -1) {
                value = value + 'px';
            }

            if (key == 'others') {
                csscontent += value + ';';
            } else {
                csscontent += key + ':' + value + ';';
            }
        });
        csscontent += '}';

        if (styles['color'] !== undefined) {
            csscontent += '#onetopic-subsectionstyles .subsection-preview-container .activityname a,';
            csscontent += '#onetopic-subsectionstyles .subsection-preview-container .sectionname a';
            csscontent += '{color:' + styles['color'] + ';}';
        }
    }

    var $stylecontainer = $container.find('style');

    if ($stylecontainer.length > 0) {
        $stylecontainer.remove();
    }

    $stylecontainer = $('<style type="text/css">' + csscontent + '</style>');
    $container.append($stylecontainer);

};
