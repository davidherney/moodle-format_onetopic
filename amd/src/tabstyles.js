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
 * @copyright 2023 David Herney Bernal - cirano
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import $ from 'jquery';
import ModalFactory from 'core/modal_factory';

var $tabstyles = null;
var $styleswindow = null;
var currenttype = 'default';
var globalstyles = {};
var $inputtosave = null;

/**
 * Component initialization.
 *
 * @method init
 */
export const init = () => {

    $tabstyles = $('#onetopic-tabstyles');
    $inputtosave = $tabstyles.find('textarea.savecontrol');
    $styleswindow = $('#onetopic-styleswindow');

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

    // Save the styles.
    $styleswindow.find('[data-action="cancelstyles"]').on('click', function() {
        $styleswindow.data('modal').hide();
    });

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

        globalstyles[currenttype] = newstyles;
        $inputtosave.val(JSON.stringify(globalstyles));
        applyStyles();

        modal.hide();
    });

    var types = ['default', 'active', 'parent', 'highlighted', 'disabled', 'hover', 'childs', 'childindex'];
    types.forEach(type => {
        $('#onetopic-tabstyles #tabstyleset' + type).on('click', function(e) {
            e.preventDefault();
            showStylesWindow(type);
        });
    });

    $('#onetopic-tabstyles #tabstyleclear').on('click', function(e) {
        e.preventDefault();
        globalstyles = {};
        $inputtosave.val('');
        applyStyles();
    });

};

/**
 * Show the styles window.
 *
 * @param {string} type
 */
var showStylesWindow = function(type) {

    currenttype = type;
    $styleswindow.find('[data-style]').each(function() {
        var $node = $(this);

        if ($node.is('input[type="text"]')) {
            $node.val('');
            $node.trigger('change');
        } else if ($node.is('select')) {
            $node.find('option').first().prop('selected', true);
        }
    });

    if (globalstyles[type] !== undefined) {
        var currentstyles = globalstyles[type];

        Object.entries(currentstyles).forEach(([key, value]) => {
            $styleswindow.find('[data-style="' + key + '"]').val(value);

            // Apply the color.
            $styleswindow.find('[data-control="colorpicker"]').trigger('change');
        });
    }

    $styleswindow.data('modal').show();

};

/**
 * Apply the styles to the tabs for check current visualization.
 */
var applyStyles = function() {

    var withunits = ['font-size', 'line-height', 'margin', 'padding', 'border-width', 'border-radius'];
    var csscontent = '';

    Object.entries(globalstyles).forEach(([type, styles]) => {

        switch (type) {
            case 'active':
                csscontent += '.format-onetopic .verticaltabs .format_onetopic-tabs .nav-item a.nav-link.active, ';
                csscontent += '.format-onetopic .nav-tabs a.nav-link.active, ';
                csscontent += '.format-onetopic .onetopic-tab-body .nav-tabs a.nav-link.active, ';
                csscontent += '.format-onetopic .onetopic-tab-body .nav-tabs .nav-item.subtopic a.nav-link.active';
            break;
            case 'parent':
                csscontent += '.format-onetopic .verticaltabs .format_onetopic-tabs .nav-item.haschilds a.nav-link, ';
                csscontent += '.format-onetopic .nav-tabs .nav-item.haschilds a.nav-link';
            break;
            case 'highlighted':
                csscontent += '.format-onetopic .verticaltabs .format_onetopic-tabs .nav-item.marker a.nav-link, ';
                csscontent += '.format-onetopic .nav-tabs .nav-item.marker a.nav-link, ';
                csscontent += '.format-onetopic .onetopic-tab-body .nav-tabs .nav-item.marker a.nav-link, ';
                csscontent += '.format-onetopic .onetopic-tab-body .nav-tabs .nav-item.subtopic.marker a.nav-link';
            break;
            case 'disabled':
                csscontent += '.format-onetopic .verticaltabs .format_onetopic-tabs .nav-item.disabled a.nav-link, ';
                csscontent += '.format-onetopic .nav-tabs .nav-item.disabled a.nav-link, ';
                csscontent += '.format-onetopic .onetopic-tab-body .nav-tabs .nav-item.disabled a.nav-link, ';
                csscontent += '.format-onetopic .onetopic-tab-body .nav-tabs .nav-item.subtopic.disabled a.nav-link';
            break;
            case 'hover':
                csscontent += '.format-onetopic .verticaltabs .format_onetopic-tabs .nav-item a.nav-link:hover, ';
                csscontent += '.format-onetopic .nav-tabs .nav-item a.nav-link:hover, ';
                csscontent += '.format-onetopic .onetopic-tab-body .nav-tabs .nav-item a.nav-link:hover, ';
                csscontent += '.format-onetopic .onetopic-tab-body .nav-tabs .nav-item.subtopic a.nav-link:hover';
            break;
            case 'childs':
                csscontent += '.format-onetopic .onetopic-tab-body .nav-tabs .nav-item.subtopic a.nav-link';
            break;
            case 'childindex':
                csscontent += '.format-onetopic .onetopic-tab-body .nav-tabs .nav-item.subtopic.tab_initial a.nav-link';
            break;
            default:
                csscontent += '.format-onetopic .verticaltabs .format_onetopic-tabs .nav-item a.nav-link, ';
                csscontent += '.format-onetopic .nav-tabs a.nav-link';
        }

        csscontent += '{';
        var units = [];
        var stylesarray = Object.entries(styles);

        // Check if exist units for some rules.
        stylesarray.forEach(([key, value]) => {
            // Check if the key start with the units prefix.
            if (key.indexOf('unit-') === 0) {

                // Remove the prefix.
                key = key.replace('unit-', '');
                units[key] = value;
            }
        });

        stylesarray.forEach(([key, value]) => {

            // Exclude the units rules.
            if (key.indexOf('unit-') === 0) {
                return;
            }

            // If exist a unit for the rule, apply it.
            if (units[key] !== undefined) {
                value = value + units[key];
            } else if (withunits.indexOf(key) !== -1) {
                // If the rule need units, apply px by default.
                value = value + 'px';
            }

            if (key == 'others') {
                csscontent += value + ';';
            } else {
                csscontent += key + ':' + value + ';';
            }
        });
        csscontent += '}';
    });

    var $stylecontainer = $tabstyles.find('style');

    if ($stylecontainer.length > 0) {
        $stylecontainer.remove();
    }

    $stylecontainer = $('<style type="text/css">' + csscontent + '</style>');
    $tabstyles.append($stylecontainer);

};
