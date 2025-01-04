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
 * Used to oneline tabs view.
 *
 * @module    format_onetopic/oneline
 * @copyright 2021 David Herney Bernal - cirano
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';

/**
 * Load tabs in a single line, with scroll if it is required.
 *
 * @method load
 * @param {object} icons A list of usable icons: left arrow, right arrow.
 */
 export const load = (icons) => {

    $('.format-onetopic .onelinetabs .tabs-wrapper').each(function() {

        var $container = $(this);

        var $menu = $container.find('> ul.nav.nav-tabs');

        var itemsLength = $menu.find('> li.nav-item').length;
        var itemSize = $menu.find('> li.nav-item').outerWidth(true);

        // Duration of scroll animation.
        var scrollDuration = 300;

        // Get some relevant size for the paddle triggering point.
        var paddleMargin = 20;

        var $leftarrow = $('<button class="scroll-arrow left-arrow">' + icons.left + '</button>');
        var $rightarrow = $('<button class="scroll-arrow right-arrow">' + icons.right + '</button>');

        $container.append($leftarrow);
        $container.append($rightarrow);

        // Get total width of all menu items.
        var getMenuSize = function() {
            return itemsLength * itemSize;
        };

        // Get how much have we scrolled to the left.
        var getMenuPosition = function() {
            return $menu.scrollLeft();
        };

        // Get wrapper width.
        var getMenuWrapperSize = function() {
            return $container.width();
        };

        // Get wrapper padding.
        var getMenuWrapperPadding = function() {
            return $container.outerWidth() - $container.width();
        };

        // Check if has scoll.
        var hasScroll = function() {
            return $menu.get(0).scrollWidth > $menu.get(0).clientWidth;
        };

        var getInvisibleSize = function() {
            return menuSize - getMenuPosition() - menuWrapperSize;
        };

        var getMaxScrollLeft = function() {
            return $menu.get(0).scrollWidth - $menu.get(0).clientWidth;// - menuWrapperSize;
        };

        if (hasScroll()) {
            $container.addClass('hasscroll');
        }

        var menuWrapperSize = getMenuWrapperSize();
        var wrapPadding = getMenuWrapperPadding();

        var menuSize = getMenuSize();

        // Get how much of menu is invisible.
        var menuInvisibleSize = getInvisibleSize();

        // The wrapper is responsive.
        $(window).on('resize', function() {
            menuWrapperSize = getMenuWrapperSize();
            menuSize = getMenuSize();
            calcArrowVisible();
        });

        var calcArrowVisible = function() {
            // Get how much have we scrolled so far.
            var menuPosition = getMenuPosition();

            // Get how much of menu is invisible.
            menuInvisibleSize = getInvisibleSize();

            var maxScrollLeft = getMaxScrollLeft();

            var menuEndOffset = menuInvisibleSize - paddleMargin;

            // Show & hide the paddles depending on scroll position.
            if (!hasScroll()) {
                $leftarrow.addClass('hidden');
                $rightarrow.addClass('hidden');
                $container.removeClass('hasscroll');
            } else {
                $container.addClass('hasscroll');
                if (menuPosition <= paddleMargin) {
                    $leftarrow.addClass('hidden');
                    $rightarrow.removeClass('hidden');
                } else if (menuPosition < maxScrollLeft) {
                    // Show both paddles in the middle.
                    $leftarrow.removeClass('hidden');
                    $rightarrow.removeClass('hidden');
                } else if (menuPosition >= menuEndOffset) {
                    $leftarrow.removeClass('hidden');
                    $rightarrow.addClass('hidden');
                }
            }
        };

        // Finally, what happens when we are actually scrolling the menu.
        $menu.on('scroll', function() {
            calcArrowVisible();
        });

        // Scroll to left.
        $rightarrow.on('click', function(event) {
            event.preventDefault();
            var newX = getMenuPosition() + (menuWrapperSize * 0.8);
            $menu.animate({scrollLeft: newX}, scrollDuration);
        });

        // Scroll to right.
        $leftarrow.on('click', function(event) {
            event.preventDefault();
            var newX = getMenuPosition() - (menuWrapperSize * 0.8);
            $menu.animate({scrollLeft: newX}, scrollDuration);
        });

        // Change position to see the active tab.
        var $active = $container.find('.nav-link.active');
        if ($active.length > 0) {
            var newX = $active.position().left - (wrapPadding / 2);
            $menu.animate({scrollLeft: newX}, scrollDuration);
        }

        calcArrowVisible();
    });
};
