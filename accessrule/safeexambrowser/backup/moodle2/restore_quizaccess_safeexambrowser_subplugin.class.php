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
 * Restore code for the quizaccess_safeexambrowser plugin.
 *
 * @package   finalquizaccess_safeexambrowser
 * @copyright 2013 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/finalquiz/backup/moodle2/restore_mod_finalquiz_access_subplugin.class.php');
require_once($CFG->dirroot . '/mod/finalquiz/accessrule/safeexambrowser/rule.php');


/**
 * Provides the information to restore the honestycheck finalquiz access plugin.
 *
 * The XML looks like
 * <finalquizaccess_safeexambrowser>
 *     <keys>0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef
 * 1123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef</keys>
 * </finalquizaccess_safeexambrowser>
 * If there are any keys, they need to be inserted into the DB. Otherwise, nothing
 * needs to be written to the DB.
 *
 * @copyright 2013 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_finalquizaccess_safeexambrowser_subplugin extends restore_mod_finalquiz_access_subplugin {

    /**
     * Provides path structure required to restore data for safeexambrowser final quiz access plugin.
     *
     * @return array
     */
    protected function define_finalquiz_subplugin_structure() {
        $paths = array();

        $elename = $this->get_namefor('');
        $elepath = $this->get_pathfor('/finalquizaccess_safeexambrowser');
        $paths[] = new restore_path_element($elename, $elepath);

        return $paths;
    }

    /**
     * Processes the finalquizaccess_safeexambrowser element, if it is in the file.
     * @param array $data the data read from the XML file.
     */
    public function process_finalquizaccess_safeexambrowser($data) {
        global $DB;

        $data = (object)$data;
        $data->quizid = $this->get_new_parentid('finalquiz');
        $data->allowedkeys = finalquizaccess_safeexambrowser::clean_keys($data->allowedkeys);
        $DB->insert_record('finalquizaccess_safeexambrowser', $data);
    }
}
