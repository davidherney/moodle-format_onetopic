<?php
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
 * Version details.
 *
 * Component releases:
 * Un recorderis de las Veredas de mi pueblo, en homenaje a los campesinos de mi tierra.
 *
 * Old releases: Guarango, Pantalio, Chalarca, Mazorcal, Chuscalito, Las Teresas, La Madera, Las Brisas, Buenavista,
 * San Juan, La Almería, Piedras Teherán, El Cardal.
 *
 * Next releases: Santa Cruz, Vallejuelito, Fátima, La Cabaña, La Palmera, Las Acacias, Las Colmenas, Minitas,
 * Quebrada Negra, San Francisco, San Miguel Abajo, San Miguel, La Concha, La Divisa
 *
 * @package format_onetopic
 * @copyright 2015 David Herney - cirano. https://bambuco.co
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version = 2024050905; // The current plugin version (Date: YYYYMMDDXX).
$plugin->requires = 2024041600; // Requires this Moodle version.
$plugin->component = 'format_onetopic'; // Full name of the plugin (used for diagnostics).
$plugin->maturity = MATURITY_STABLE;
$plugin->release = '4.4.05(LasAcacias)';
$plugin->dependencies = ['format_topics' => 2024042200];
$plugin->supported = [404, 405];
