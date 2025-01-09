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
 * Tab editor in site settings.
 *
 * @module    format_onetopic/tabstyles
 * @copyright 2021 David Herney Bernal - cirano
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

    // Define the tab icons.
    $('#onetopic-tabstyles .tpl-tabdefault .tabicon').each(function() {
        var $tabicon = $(this);
        $tabicon.append('<span class="tabicon-default hidden"></span>');
        $tabicon.append('<span class="tabicon-hover hidden"></span>');
    });

    $('#onetopic-tabstyles .tpl-tabactive .tabicon').each(function() {
        var $tabicon = $(this);
        $tabicon.append('<span class="tabicon-active hidden"></span>');
        $tabicon.append('<span class="tabicon-hover hidden"></span>');
    });

    $('#onetopic-tabstyles .tpl-tabparent .tabicon').each(function() {
        var $tabicon = $(this);
        $tabicon.append('<span class="tabicon-parent hidden"></span>');
        $tabicon.append('<span class="tabicon-default hidden"></span>');
        $tabicon.append('<span class="tabicon-hover hidden"></span>');
    });

    $('#onetopic-tabstyles .tpl-tabchildindex .tabicon').each(function() {
        var $tabicon = $(this);
        $tabicon.append('<span class="tabicon-childs hidden"></span>');
        $tabicon.append('<span class="tabicon-childindex hidden"></span>');
        $tabicon.append('<span class="tabicon-default hidden"></span>');
        $tabicon.append('<span class="tabicon-hover hidden"></span>');
    });

    $('#onetopic-tabstyles .tpl-tabchild .tabicon').each(function() {
        var $tabicon = $(this);
        $tabicon.append('<span class="tabicon-childs hidden"></span>');
        $tabicon.append('<span class="tabicon-default hidden"></span>');
        $tabicon.append('<span class="tabicon-hover hidden"></span>');
    });

    $('#onetopic-tabstyles .tpl-tabhighlighted .tabicon').each(function() {
        var $tabicon = $(this);
        $tabicon.append('<span class="tabicon-highlighted hidden"></span>');
        $tabicon.append('<span class="tabicon-default hidden"></span>');
        $tabicon.append('<span class="tabicon-hover hidden"></span>');
    });

    $('#onetopic-tabstyles .tpl-tabdisabled .tabicon').each(function() {
        var $tabicon = $(this);
        $tabicon.append('<span class="tabicon-disabled hidden"></span>');
    });

    $('#onetopic-tabstyles .tabicon').each(function() {
        $(this).removeClass('hidden');
    });
    // End of Define the tab icons.

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

    $('#tabstylesdisplay').on('click', function(e) {
        e.preventDefault();
        $tabstyles.toggleClass('hidden');
    });

    $('#onetopic-styleswindow .onetopic-selecticon').each(function() {
        var $selecticon = $(this);
        $selecticon.find('button').on('click', function(e) {
            e.preventDefault();
            $selecticon.find('.listicons').removeClass('hidden');
        });

        $selecticon.find('.listicons span').on('click', function(e) {
            e.preventDefault();
            var $icon = $(this);
            $selecticon.find('.iconselected').html($icon.html());
            $selecticon.find('[data-style="tabicon"]').val($icon.data('value'));
            $selecticon.find('.listicons').addClass('hidden');
        });

        $selecticon.find('[data-style="tabicon"]').on('change', function() {
            var $node = $(this);

            if ($node.val() === '') {
                $selecticon.find('.iconselected').html('');
                return;
            }

            var $icon = $selecticon.find('.listicons span[data-value="' + $node.val() + '"]');

            if ($icon.length > 0) {
                $selecticon.find('.iconselected').html($icon.html());
            }
        });
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

        if ($node.is('input[type="text"]') || $node.is('input[type="hidden"]')) {
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

            // Change the icon.
            $styleswindow.find('[data-style="tabicon"]').trigger('change');
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
                csscontent += '#onetopic-tabstyles .verticaltabs .format_onetopic-tabs .nav-item a.nav-link.active, ';
                csscontent += '#onetopic-tabstyles .nav-tabs a.nav-link.active, ';
                csscontent += '#onetopic-tabstyles .onetopic-tab-body .nav-tabs a.nav-link.active, ';
                csscontent += '#onetopic-tabstyles .onetopic-tab-body .nav-tabs .nav-item.subtopic a.nav-link.active';
            break;
            case 'parent':
                csscontent += '#onetopic-tabstyles .verticaltabs .format_onetopic-tabs .nav-item.haschilds a.nav-link, ';
                csscontent += '#onetopic-tabstyles .nav-tabs .nav-item.haschilds a.nav-link';
            break;
            case 'highlighted':
                csscontent += '#onetopic-tabstyles .verticaltabs .format_onetopic-tabs .nav-item.marker a.nav-link, ';
                csscontent += '#onetopic-tabstyles .nav-tabs .nav-item.marker a.nav-link, ';
                csscontent += '#onetopic-tabstyles .onetopic-tab-body .nav-tabs .nav-item.marker a.nav-link, ';
                csscontent += '#onetopic-tabstyles .onetopic-tab-body .nav-tabs .nav-item.subtopic.marker a.nav-link';
            break;
            case 'disabled':
                csscontent += '#onetopic-tabstyles .verticaltabs .format_onetopic-tabs .nav-item.disabled a.nav-link, ';
                csscontent += '#onetopic-tabstyles .nav-tabs .nav-item.disabled a.nav-link, ';
                csscontent += '#onetopic-tabstyles .onetopic-tab-body .nav-tabs .nav-item.disabled a.nav-link, ';
                csscontent += '#onetopic-tabstyles .onetopic-tab-body .nav-tabs .nav-item.subtopic.disabled a.nav-link';
            break;
            case 'hover':
                csscontent += '#onetopic-tabstyles .verticaltabs .format_onetopic-tabs .nav-item a.nav-link:hover, ';
                csscontent += '#onetopic-tabstyles .nav-tabs .nav-item a.nav-link:hover, ';
                csscontent += '#onetopic-tabstyles .onetopic-tab-body .nav-tabs .nav-item a.nav-link:hover, ';
                csscontent += '#onetopic-tabstyles .onetopic-tab-body .nav-tabs .nav-item.subtopic a.nav-link:hover';
            break;
            case 'childs':
                csscontent += '#onetopic-tabstyles .onetopic-tab-body .nav-tabs .nav-item.subtopic a.nav-link';
            break;
            case 'childindex':
                csscontent += '#onetopic-tabstyles .onetopic-tab-body .nav-tabs .nav-item.subtopic.tab_initial a.nav-link';
            break;
            default:
                csscontent += '#onetopic-tabstyles .verticaltabs .format_onetopic-tabs .nav-item a.nav-link, ';
                csscontent += '#onetopic-tabstyles .nav-tabs a.nav-link';
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
            } else if (key == 'tabicon') {
                if (value !== '') {
                    var icon = $('#onetopic-styleswindow .listicons span[data-value="' + value + '"]').html();
                    $('#onetopic-tabstyles .tabicon-' + type).html(icon).removeClass('hidden');
                }
            }
        });

        stylesarray.forEach(([key, value]) => {

            // Exclude the tab icons and units rules.
            if (key.indexOf('unit-') === 0) {
                return;
            } else if (key == 'tabicon') {
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
