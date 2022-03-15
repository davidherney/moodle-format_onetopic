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
 * @copyright 2021 David Herney Bernal - cirano
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
import * as OneLine from 'format_onetopic/oneline';

/**
 * Component initialization.
 *
 * @method init
 * @param {string} formattype The course format type: 0: default, 1: vertical, 2: oneline.
 * @param {object} icons A list of usable icons: left arrow, right arrow.
 */
export const init = (formattype, icons) => {

    if (formattype == 2) {
        OneLine.load(icons);
    }
};
