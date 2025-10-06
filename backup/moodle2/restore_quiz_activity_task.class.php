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
 * @package    mod_finalquiz
 * @subpackage backup-moodle2
 * @copyright  2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/finalquiz/backup/moodle2/restore_finalquiz_stepslib.php');


/**
 * finalquiz restore task that provides all the settings and steps to perform one
 * complete restore of the activity
 *
 * @copyright  2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_finalquiz_activity_task extends restore_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // finalQuiz only has one structure step.
        $this->add_step(new restore_finalquiz_activity_structure_step('finalquiz_structure', 'finalquiz.xml'));
    }

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder
     */
    public static function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('finalquiz', array('intro'), 'finalquiz');
        $contents[] = new restore_decode_content('finalquiz_feedback',
                array('feedbacktext'), 'finalquiz_feedback');

        return $contents;
    }

    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder
     */
    public static function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('QUIZVIEWBYID',
                '/mod/finalquiz/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('QUIZVIEWBYQ',
                '/mod/finalquiz/view.php?q=$1', 'finalquiz');
        $rules[] = new restore_decode_rule('QUIZINDEX',
                '/mod/finalquiz/index.php?id=$1', 'course');

        return $rules;

    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * finalquiz logs. It must return one array
     * of {@link restore_log_rule} objects
     */
    public static function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('finalquiz', 'add',
                'view.php?id={course_module}', '{finalquiz}');
        $rules[] = new restore_log_rule('finalquiz', 'update',
                'view.php?id={course_module}', '{finalquiz}');
        $rules[] = new restore_log_rule('finalquiz', 'view',
                'view.php?id={course_module}', '{finalquiz}');
        $rules[] = new restore_log_rule('finalquiz', 'preview',
                'view.php?id={course_module}', '{finalquiz}');
        $rules[] = new restore_log_rule('finalquiz', 'report',
                'report.php?id={course_module}', '{finalquiz}');
        $rules[] = new restore_log_rule('finalquiz', 'editquestions',
                'view.php?id={course_module}', '{finalquiz}');
        $rules[] = new restore_log_rule('finalquiz', 'delete attempt',
                'report.php?id={course_module}', '[oldattempt]');
        $rules[] = new restore_log_rule('finalquiz', 'edit override',
                'overrideedit.php?id={finalquiz_override}', '{finalquiz}');
        $rules[] = new restore_log_rule('finalquiz', 'delete override',
                'overrides.php.php?cmid={course_module}', '{finalquiz}');
        $rules[] = new restore_log_rule('finalquiz', 'addcategory',
                'view.php?id={course_module}', '{question_category}');
        $rules[] = new restore_log_rule('finalquiz', 'view summary',
                'summary.php?attempt={finalquiz_attempt}', '{finalquiz}');
        $rules[] = new restore_log_rule('finalquiz', 'manualgrade',
                'comment.php?attempt={finalquiz_attempt}&question={question}', '{finalquiz}');
        $rules[] = new restore_log_rule('finalquiz', 'manualgrading',
                'report.php?mode=grading&q={finalquiz}', '{finalquiz}');
        // All the ones calling to review.php have two rules to handle both old and new urls
        // in any case they are always converted to new urls on restore.
        // TODO: In Moodle 2.x (x >= 5) kill the old rules.
        // Note we are using the 'finalquiz_attempt' mapping because that is the
        // one containing the finalquiz_attempt->ids old an new for finalquiz-attempt.
        $rules[] = new restore_log_rule('finalquiz', 'attempt',
                'review.php?id={course_module}&attempt={finalquiz_attempt}', '{finalquiz}',
                null, null, 'review.php?attempt={finalquiz_attempt}');
        $rules[] = new restore_log_rule('finalquiz', 'attempt',
                'review.php?attempt={finalquiz_attempt}', '{finalquiz}',
                null, null, 'review.php?attempt={quiz_attempt}');
        // Old an new for finalquiz-submit.
        $rules[] = new restore_log_rule('finalquiz', 'submit',
                'review.php?id={course_module}&attempt={finalquiz_attempt}', '{finalquiz}',
                null, null, 'review.php?attempt={finalquiz_attempt}');
        $rules[] = new restore_log_rule('finalquiz', 'submit',
                'review.php?attempt={finalquiz_attempt}', '{finalquiz}');
        // Old an new for quiz-review.
        $rules[] = new restore_log_rule('finalquiz', 'review',
                'review.php?id={course_module}&attempt={finalquiz_attempt}', '{finalquiz}',
                null, null, 'review.php?attempt={finalquiz_attempt}');
        $rules[] = new restore_log_rule('finalquiz', 'review',
                'review.php?attempt={finalquiz_attempt}', '{finalquiz}');
        // Old an new for quiz-start attemp.
        $rules[] = new restore_log_rule('finalquiz', 'start attempt',
                'review.php?id={course_module}&attempt={finalquiz_attempt}', '{finalquiz}',
                null, null, 'review.php?attempt={finalquiz_attempt}');
        $rules[] = new restore_log_rule('finalquiz', 'start attempt',
                'review.php?attempt={finalquiz_attempt}', '{finalquiz}');
        // Old an new for quiz-close attemp.
        $rules[] = new restore_log_rule('finalquiz', 'close attempt',
                'review.php?id={course_module}&attempt={finalquiz_attempt}', '{finalquiz}',
                null, null, 'review.php?attempt={finalquiz_attempt}');
        $rules[] = new restore_log_rule('finalquiz', 'close attempt',
                'review.php?attempt={finalquiz_attempt}', '{finalquiz}');
        // Old an new for quiz-continue attempt.
        $rules[] = new restore_log_rule('finalquiz', 'continue attempt',
                'review.php?id={course_module}&attempt={finalquiz_attempt}', '{finalquiz}',
                null, null, 'review.php?attempt={finalquiz_attempt}');
        $rules[] = new restore_log_rule('finalquiz', 'continue attempt',
                'review.php?attempt={finalquiz_attempt}', '{finalquiz}');
        // Old an new for quiz-continue attemp.
        $rules[] = new restore_log_rule('finalquiz', 'continue attemp',
                'review.php?id={course_module}&attempt={finalquiz_attempt}', '{finalquiz}',
                null, 'continue attempt', 'review.php?attempt={finalquiz_attempt}');
        $rules[] = new restore_log_rule('finalquiz', 'continue attemp',
                'review.php?attempt={finalquiz_attempt}', '{finalquiz}',
                null, 'continue attempt');

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * course logs. It must return one array
     * of {@link restore_log_rule} objects
     *
     * Note this rules are applied when restoring course logs
     * by the restore final task, but are defined here at
     * activity level. All them are rules not linked to any module instance (cmid = 0)
     */
    public static function define_restore_log_rules_for_course() {
        $rules = array();

        $rules[] = new restore_log_rule('finalquiz', 'view all', 'index.php?id={course}', null);

        return $rules;
    }
}
