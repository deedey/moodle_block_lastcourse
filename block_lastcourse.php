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
 * 
 *
 * @package    block_lastcourse
 * @copyright  2018 Dey Bendifallah
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_lastcourse extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_lastcourse');
    }

    public function get_content() {
        global $USER, $DB, $CFG;
        $verif_last_course = $DB->count_records('logstore_standard_log', array('action' => "viewed",
                    'target' => "course", 'userid' => $USER->id));
        $this->content = new stdClass();
        if ($verif_last_course == 0)
        {
            $this->content->text = html_writer::div('<span style="color:#FF0000;font-weight: bold;"'.
                 ' title="'.get_string('lastcourse_courselearned', 'block_lastcourse').'">'.
                 get_string('lastcourse_nocourse', 'block_lastcourse').'</span>');
        }
        else
        {
           $last_course = $DB->get_records_sql('SELECT * FROM {logstore_standard_log} WHERE '.
               'action = ?  AND target = ? AND userid = ? order by timecreated desc limit 0,1',
               array('viewed', 'course',$USER->id));
           $i=0;
           foreach($last_course as $record)
           {
              if ($i>0)
                 break;
              $cours = '/course/view.php?id='.$record->courseid;
              $i++;
           }
           $urlcours = new moodle_url($cours);
           $this->content->text = html_writer::link($urlcours,get_string('lastcourse_mylastcourse', 'block_lastcourse'), array('target' => '_self'));
           $verif_last_asset = $DB->count_records('logstore_standard_log', array('action' => "viewed",
                           'target' => "course_module", 'userid' => $USER->id));
           if ($verif_last_asset > 0)
           {
              $last_asset = $DB->get_records_sql('SELECT * FROM {logstore_standard_log} WHERE '.
                   'action = ?  AND target = ? AND userid = ? order by timecreated desc limit 0, 1', array('viewed', 'course_module', $USER->id));
              $j=0;
              foreach($last_asset as $record)
              {
                 if ($j>0)
                    break;
                 $asset = '/mod/'.$record->objecttable.'/view.php?id='.$record->contextinstanceid;
                 $j++;
              }
              $url_asset = new moodle_url($asset);
              $this->content->text .= html_writer::link($url_asset, "<br>".get_string('lastcourse_mylastmodule', 'block_lastcourse'), array('target' => '_self'));
           }
        }
        $this->content->footer = '';
        return $this->content;
    }
}
