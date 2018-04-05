<?php
// This file is part of Moodle - http://moodle.org/
//
//
// This file and block dependencies needs a free subscription to http://lrs.annulab.com
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
 * annulabLRS block.
 *
 * @package    block_xapi_lrs
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
class block_lastcourse extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_lastcourse');
    }

    public function get_content() {
        global $USER, $_SERVER, $DB, $CFG;
        $VerifLastCourse = $DB->count_records('logstore_standard_log',array('action' => "viewed",
                    'target' => "course", 'userid' => $USER->id));
        $this->content = new stdClass();
        if ($VerifLastCourse == 0)
        {
            $this->content->text = html_writer::div('<span style="color:#FF0000;font-weight: bold;"'.
                 ' title="'.get_string('lastcourse_courselearned', 'block_lastcourse').'">'.
                 get_string('lastcourse_nocourse', 'block_lastcourse').'</span>');
        }
        else
        {
           $LastCourse = $DB->get_records_sql('SELECT * FROM {logstore_standard_log} WHERE '.
               'action = ?  AND target = ? AND userid = ? order by timecreated desc limit 0,1',
               array('viewed', 'course',$USER->id));
           $i=0;
           foreach($LastCourse as $record)
           {
              if ($i>0)
                 break;
              $Cours = '/course/view.php?id='.$record->courseid;
              $i++;
           }
           $urlCours = new moodle_url($Cours);
           $this->content->text = html_writer::link($urlCours,get_string('lastcourse_mylastcourse', 'block_lastcourse'),array('target' => '_self'));
           $VerifLastAsset = $DB->count_records('logstore_standard_log',array('action' => "viewed",
                           'target' => "course_module", 'userid' => $USER->id));
           if ($VerifLastAsset > 0)
           {
              $LastAsset = $DB->get_records_sql('SELECT * FROM {logstore_standard_log} WHERE '.
                   'action = ?  AND target = ? AND userid = ? order by timecreated desc limit 0,1',
                   array('viewed', 'course_module',$USER->id));
              $j=0;
              foreach($LastAsset as $record)
              {
                 if ($j>0)
                    break;
                 $Asset = '/mod/'.$record->objecttable.'/view.php?id='.$record->contextinstanceid;
                 $j++;
              }
              $urlAsset = new moodle_url($Asset);
              $this->content->text .= html_writer::link($urlAsset,"<br>".get_string('lastcourse_mylastmodule', 'block_lastcourse'),
                                      array('target' => '_self'));
           }
        }
        $this->content->footer = '';
        return $this->content;
    }
}
?>
