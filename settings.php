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
 * Administration settings definitions for the quiz module.
 *
 * @package   mod_finalquiz
 * @copyright 2010 Petr Skoda
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/finalquiz/lib.php');

// First get a list of final quiz reports with there own settings pages. If there none,
// we use a simpler overall menu structure.
$reports = core_component::get_plugin_list_with_file('finalquiz', 'settings.php', false);
$reportsbyname = array();
foreach ($reports as $report => $reportdir) {
    $strreportname = get_string($report . 'report', 'finalquiz_'.$report);
    $reportsbyname[$strreportname] = $report;
}
core_collator::ksort($reportsbyname);

// First get a list of final quiz reports with there own settings pages. If there none,
// we use a simpler overall menu structure.
$rules = core_component::get_plugin_list_with_file('finalquizaccess', 'settings.php', false);
$rulesbyname = array();
foreach ($rules as $rule => $ruledir) {
    $strrulename = get_string('pluginname', 'finalquizaccess_' . $rule);
    $rulesbyname[$strrulename] = $rule;
}
core_collator::ksort($rulesbyname);

// Create the final quiz settings page.
if (empty($reportsbyname) && empty($rulesbyname)) {
    $pagetitle = get_string('modulename', 'finalquiz');
} else {
    $pagetitle = get_string('generalsettings', 'admin');
}
$quizsettings = new admin_settingpage('modsettingfinalquiz', $pagetitle, 'moodle/site:config');

if ($ADMIN->fulltree) {
    // Introductory explanation that all the settings are defaults for the add quiz form.
    $quizsettings->add(new admin_setting_heading('finalquizintro', '', get_string('configintro', 'finalquiz')));

    // Time limit.
    $quizsettings->add(new admin_setting_configduration_with_advanced('finalquiz/timelimit',
            get_string('timelimit', 'finalquiz'), get_string('configtimelimitsec', 'finalquiz'),
            array('value' => '0', 'adv' => false), 60));

    // What to do with overdue attempts.
    $quizsettings->add(new mod_finalquiz_admin_setting_overduehandling('finalquiz/overduehandling',
            get_string('overduehandling', 'finalquiz'), get_string('overduehandling_desc', 'finalquiz'),
            array('value' => 'autosubmit', 'adv' => false), null));

    // Grace period time.
    $quizsettings->add(new admin_setting_configduration_with_advanced('finalquiz/graceperiod',
            get_string('graceperiod', 'finalquiz'), get_string('graceperiod_desc', 'finalquiz'),
            array('value' => '86400', 'adv' => false)));

    // Minimum grace period used behind the scenes.
    $quizsettings->add(new admin_setting_configduration('finalquiz/graceperiodmin',
            get_string('graceperiodmin', 'finalquiz'), get_string('graceperiodmin_desc', 'finalquiz'),
            60, 1));

    // Number of attempts.
    $options = array(get_string('unlimited'));
    for ($i = 1; $i <= FINALQUIZ_MAX_ATTEMPT_OPTION; $i++) {
        $options[$i] = $i;
    }
    $quizsettings->add(new admin_setting_configselect_with_advanced('finalquiz/attempts',
            get_string('attemptsallowed', 'finalquiz'), get_string('configattemptsallowed', 'finalquiz'),
            array('value' => 0, 'adv' => false), $options));

    // Grading method.
    $quizsettings->add(new mod_finalquiz_admin_setting_grademethod('finalquiz/grademethod',
            get_string('grademethod', 'finalquiz'), get_string('configgrademethod', 'finalquiz'),
            array('value' => FINALQUIZ_GRADEHIGHEST, 'adv' => false), null));

    // Maximum grade.
    $quizsettings->add(new admin_setting_configtext('finalquiz/maximumgrade',
            get_string('maximumgrade'), get_string('configmaximumgrade', 'finalquiz'), 10, PARAM_INT));

    // Questions per page.
    $perpage = array();
    $perpage[0] = get_string('never');
    $perpage[1] = get_string('aftereachquestion', 'finalquiz');
    for ($i = 2; $i <= FINALQUIZ_MAX_QPP_OPTION; ++$i) {
        $perpage[$i] = get_string('afternquestions', 'finalquiz', $i);
    }
    $quizsettings->add(new admin_setting_configselect_with_advanced('finalquiz/questionsperpage',
            get_string('newpageevery', 'finalquiz'), get_string('confignewpageevery', 'finalquiz'),
            array('value' => 1, 'adv' => false), $perpage));

    // Navigation method.
    $quizsettings->add(new admin_setting_configselect_with_advanced('finalquiz/navmethod',
            get_string('navmethod', 'finalquiz'), get_string('confignavmethod', 'finalquiz'),
            array('value' => FINALQUIZ_NAVMETHOD_FREE, 'adv' => true), finalquiz_get_navigation_options()));

    // Shuffle within questions.
    $quizsettings->add(new admin_setting_configcheckbox_with_advanced('finalquiz/shuffleanswers',
            get_string('shufflewithin', 'finalquiz'), get_string('configshufflewithin', 'finalquiz'),
            array('value' => 1, 'adv' => false)));

    // Preferred behaviour.
    $quizsettings->add(new admin_setting_question_behaviour('finalquiz/preferredbehaviour',
            get_string('howquestionsbehave', 'question'), get_string('howquestionsbehave_desc', 'finalquiz'),
            'deferredfeedback'));

    // Can redo completed questions.
    $quizsettings->add(new admin_setting_configselect_with_advanced('finalquiz/canredoquestions',
            get_string('canredoquestions', 'finalquiz'), get_string('canredoquestions_desc', 'finalquiz'),
            array('value' => 0, 'adv' => true),
            array(0 => get_string('no'), 1 => get_string('canredoquestionsyes', 'finalquiz'))));

    // Each attempt builds on last.
    $quizsettings->add(new admin_setting_configcheckbox_with_advanced('finalquiz/attemptonlast',
            get_string('eachattemptbuildsonthelast', 'finalquiz'),
            get_string('configeachattemptbuildsonthelast', 'finalquiz'),
            array('value' => 0, 'adv' => true)));

    // Review options.
    $quizsettings->add(new admin_setting_heading('reviewheading',
            get_string('reviewoptionsheading', 'finalquiz'), ''));
    foreach (mod_finalquiz_admin_review_setting::fields() as $field => $name) {
        $default = mod_finalquiz_admin_review_setting::all_on();
        $forceduring = null;
        if ($field == 'attempt') {
            $forceduring = true;
        } else if ($field == 'overallfeedback') {
            $default = $default ^ mod_finalquiz_admin_review_setting::DURING;
            $forceduring = false;
        }
        $quizsettings->add(new mod_finalquiz_admin_review_setting('finalquiz/review' . $field,
                $name, '', $default, $forceduring));
    }

    // Show the user's picture.
    $quizsettings->add(new mod_finalquiz_admin_setting_user_image('finalquiz/showuserpicture',
            get_string('showuserpicture', 'finalquiz'), get_string('configshowuserpicture', 'finalquiz'),
            array('value' => 0, 'adv' => false), null));

    // Decimal places for overall grades.
    $options = array();
    for ($i = 0; $i <= FINALQUIZ_MAX_DECIMAL_OPTION; $i++) {
        $options[$i] = $i;
    }
    $quizsettings->add(new admin_setting_configselect_with_advanced('finalquiz/decimalpoints',
            get_string('decimalplaces', 'finalquiz'), get_string('configdecimalplaces', 'finalquiz'),
            array('value' => 2, 'adv' => false), $options));

    // Decimal places for question grades.
    $options = array(-1 => get_string('sameasoverall', 'finalquiz'));
    for ($i = 0; $i <= FINALQUIZ_MAX_Q_DECIMAL_OPTION; $i++) {
        $options[$i] = $i;
    }
    $quizsettings->add(new admin_setting_configselect_with_advanced('finalquiz/questiondecimalpoints',
            get_string('decimalplacesquestion', 'finalquiz'),
            get_string('configdecimalplacesquestion', 'finalquiz'),
            array('value' => -1, 'adv' => true), $options));

    // Show blocks during quiz attempts.
    $quizsettings->add(new admin_setting_configcheckbox_with_advanced('finalquiz/showblocks',
            get_string('showblocks', 'finalquiz'), get_string('configshowblocks', 'finalquiz'),
            array('value' => 0, 'adv' => true)));

    // Password.
    $quizsettings->add(new admin_setting_configtext_with_advanced('finalquiz/password',
            get_string('requirepassword', 'finalquiz'), get_string('configrequirepassword', 'finalquiz'),
            array('value' => '', 'adv' => false), PARAM_TEXT));

    // IP restrictions.
    $quizsettings->add(new admin_setting_configtext_with_advanced('finalquiz/subnet',
            get_string('requiresubnet', 'finalquiz'), get_string('configrequiresubnet', 'finalquiz'),
            array('value' => '', 'adv' => true), PARAM_TEXT));

    // Enforced delay between attempts.
    $quizsettings->add(new admin_setting_configduration_with_advanced('finalquiz/delay1',
            get_string('delay1st2nd', 'finalquiz'), get_string('configdelay1st2nd', 'finalquiz'),
            array('value' => 0, 'adv' => true), 60));
    $quizsettings->add(new admin_setting_configduration_with_advanced('finalquiz/delay2',
            get_string('delaylater', 'finalquiz'), get_string('configdelaylater', 'finalquiz'),
            array('value' => 0, 'adv' => true), 60));

    // Browser security.
    $quizsettings->add(new mod_finalquiz_admin_setting_browsersecurity('finalquiz/browsersecurity',
            get_string('showinsecurepopup', 'finalquiz'), get_string('configpopup', 'finalquiz'),
            array('value' => '-', 'adv' => true), null));

    $quizsettings->add(new admin_setting_configtext('finalquiz/initialnumfeedbacks',
            get_string('initialnumfeedbacks', 'finalquiz'), get_string('initialnumfeedbacks_desc', 'finalquiz'),
            2, PARAM_INT, 5));

    // Allow user to specify if setting outcomes is an advanced setting.
    if (!empty($CFG->enableoutcomes)) {
        $quizsettings->add(new admin_setting_configcheckbox('finalquiz/outcomes_adv',
            get_string('outcomesadvanced', 'finalquiz'), get_string('configoutcomesadvanced', 'finalquiz'),
            '0'));
    }

    // Autosave frequency.
    $quizsettings->add(new admin_setting_configduration('finalquiz/autosaveperiod',
            get_string('autosaveperiod', 'finalquiz'), get_string('autosaveperiod_desc', 'finalquiz'), 60, 1));
}

// Now, depending on whether any reports have their own settings page, add
// the final quiz setting page to the appropriate place in the tree.
if (empty($reportsbyname) && empty($rulesbyname)) {
    $ADMIN->add('modsettings', $quizsettings);
} else {
    $ADMIN->add('modsettings', new admin_category('modsettingsfinalquizcat',
            get_string('modulename', 'finalquiz'), $module->is_enabled() === false));
    $ADMIN->add('modsettingsfinalquizcat', $quizsettings);

    // Add settings pages for the final final quiz report subplugins.
    foreach ($reportsbyname as $strreportname => $report) {
        $reportname = $report;

        $settings = new admin_settingpage('modsettingsfinalquizcat'.$reportname,
                $strreportname, 'moodle/site:config', $module->is_enabled() === false);
        if ($ADMIN->fulltree) {
            include($CFG->dirroot . "/mod/finalquiz/report/$reportname/settings.php");
        }
        if (!empty($settings)) {
            $ADMIN->add('modsettingsfinalquizcat', $settings);
        }
    }

    // Add settings pages for the quiz access rule subplugins.
    foreach ($rulesbyname as $strrulename => $rule) {
        $settings = new admin_settingpage('modsettingsfinalquizcat' . $rule,
                $strrulename, 'moodle/site:config', $module->is_enabled() === false);
        if ($ADMIN->fulltree) {
            include($CFG->dirroot . "/mod/finalquiz/accessrule/$rule/settings.php");
        }
        if (!empty($settings)) {
            $ADMIN->add('modsettingsfinalquizcat', $settings);
        }
    }
}

$settings = null; // We do not want standard settings link.
