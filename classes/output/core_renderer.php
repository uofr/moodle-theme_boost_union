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
 * Theme Boost Union - Core renderer
 *
 * @package    theme_boost_union
 * @copyright  2022 Moodle an Hochschulen e.V. <kontakt@moodle-an-hochschulen.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_boost_union\output;

use stdClass;
use context_course;
use html_writer;
use moodle_url;

/**
 * Extending the core_renderer interface.
 *
 * @package    theme_boost_union
 * @copyright  2022 Moodle an Hochschulen e.V. <kontakt@moodle-an-hochschulen.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_renderer extends \theme_boost\output\core_renderer {

    /**
     * Returns the moodle_url for the favicon.
     *
     * This renderer function is copied and modified from /lib/outputrenderers.php
     *
     * @since Moodle 2.5.1 2.6
     * @return moodle_url The moodle_url for the favicon
     */
    public function favicon() {
        if (!empty($this->page->theme->settings->favicon)) {
            return $this->page->theme->setting_file_url('favicon', 'favicon');
        } else {
            return $this->image_url('favicon', 'theme');
        }
    }

    /**
     * Returns HTML attributes to use within the body tag. This includes an ID and classes.
     *
     * This renderer function is copied and modified from /lib/outputrenderers.php
     *
     * @since Moodle 2.5.1 2.6
     * @param string|array $additionalclasses Any additional classes to give the body tag,
     * @return string
     */
    public function body_attributes($additionalclasses = array()) {
        global $CFG;

        // Require local library.
        require_once($CFG->dirroot . '/theme/boost_union/locallib.php');

        if (!is_array($additionalclasses)) {
            $additionalclasses = explode(' ', $additionalclasses);
        }

        // If this isn't the login page and the page has a background image, add a class to the body attributes.
        if ($this->page->pagelayout != 'login') {
            if (!empty(get_config('theme_boost_union', 'backgroundimage'))) {
                $additionalclasses[] = 'backgroundimage';
            }
        }

        // If this is the login page and the page has a login background image, add a class to the body attributes.
        if ($this->page->pagelayout == 'login') {
            // Generate the background image class for displaying a random image for the login page.
            $loginimageclass = theme_boost_union_get_random_loginbackgroundimage_class();

            // If the background image class was returned, we can expect that a background image was set.
            // In this case, add both the general loginbackgroundimage class as well as the generated
            // class to the body tag.
            if ($loginimageclass != '') {
                $additionalclasses[] = 'loginbackgroundimage';
                $additionalclasses[] = $loginimageclass;
            }
        }

        return ' id="'. $this->body_id().'" class="'.$this->body_css_classes($additionalclasses).'"';
    }

    
    public function full_header() {
        // MODIFICATION START.
        global $USER, $COURSE, $CFG, $DB, $OUTPUT, $PAGE;
        $header = new stdClass();
        
        $sitecontextheader = '<div class="page-context-header"><div class="page-header-headings"><h1>'.$COURSE->fullname.'</h1></div></div>';
        
        $headertext = (!empty($this->context_header())) ? $this->context_header() : $sitecontextheader;
	
        
        //Little hack to add back missing header for dashboard
        //The context header the comes through is not formated properly
        if($this->page->pagelayout=="mydashboard"){
            $headertext = $sitecontextheader;
        }
        /*
         if (!empty($this->page->activityname)) {
         $header->contextheader = '<a href="'.$CFG->wwwroot.'/mod/'.$this->page->activityname.'/view.php?id='.$this->page->context->instanceid.'">'.$headertext.'</a>';
         } else {
         $header->contextheader = '<h2><a href="'.$CFG->wwwroot.'/course/view.php?id='.$COURSE->id.'">'.$COURSE->fullname.'</a></h2>';
         }
         */
		//error_log('pg url:'.$PAGE->url);
		
		if (strpos($PAGE->url,'grade/')===false&&strpos($PAGE->url,'backup/')===false&&strpos($PAGE->url,'reset.php')===false&&strpos($PAGE->url,'coursecompetencies.php')===false&&strpos($PAGE->url,'unenrolself.php')===false&&strpos($PAGE->url,'newbadge.php')===false&&$PAGE->url->get_param('bui_editid')===null) {
	        $header->contextheader = '<a href="'.$CFG->wwwroot.'/mod/'.$this->page->activityname.'/view.php?id='.$this->page->context->instanceid.'">'.$headertext.'</a>';
        	
		} else {
			
			$headertext = $COURSE->fullname;
	        $header->contextheader = '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$COURSE->id.'">'.$headertext.'</a>';
        
		}
		
        
        $header->mycourseheader = '<h2><a href="'.$CFG->wwwroot.'/course/view.php?id='.$COURSE->id.'" title="'.$PAGE->url.'">'.$COURSE->fullname.'</a></h2>';
        
        $header->iscoursepage = false;
        
		//$isgrader = ($PAGE->get_url()) ? : false;
		
        if (strip_tags($headertext) == $COURSE->fullname) {
            $header->contextheader = '<!-- hello -->';//'<h2><a href="'.$CFG->wwwroot.'/course/view.php?id='.$COURSE->id.'">'.$COURSE->fullname.'</a></h2>';
            $header->iscoursepage = true;
        }
        
        
        
        
        
        /*
         if (strip_tags($this->context_header()) != $COURSE->fullname) {
         $header->mycourseheader = '<h2><a href="'.$CFG->wwwroot.'/course/view.php?id='.$COURSE->id.'">'.$COURSE->fullname.'</a></h2>';
         } else {
         $header->mycourseheader = $header->contextheader;
         $header->contextheader = '<!-- hello -->';
         }
         */
        $header->instructors = $this->course_authornames();
        
        $instnum = substr_count($this->course_authornames(), 'href');
        if ($instnum > 2) {
            $header->instructnum = "largelist";
        }
        else  $header->instructnum = "smalllist";
        
        $header->navbar = $this->navbar();
        
        
        $preheader = $header->courseimage = theme_boost_union_get_course_image($COURSE);
        
        if (!$header->courseimage) {
            $header->courseimage = $OUTPUT->get_generated_image_for_id($COURSE->id);
        }
        if ($COURSE->id == 1) $header->courseimage = $CFG->wwwroot.'/theme/urcourses_default/pix/siteheader.jpg';
        
        
        $header->coursenavicon = '<a href="'.$CFG->wwwroot.'/course/view.php?id='.$COURSE->id.'"><img class="instr-avatar img-rounded" style="border-radius: 0.25em" src="'.$header->courseimage.'" height="18" width="18" title="'.$COURSE->fullname.'" alt="'.$COURSE->fullname.'" /></a>';
        
        
        $html = $this->render_from_template('theme_boost_union/full_header', $header);
        
        return $html;
        //.'<pre><code>'.$preheader.' // '.$header->courseimage.'</code></pre>';
        //'<pre><code>'.print_r($this->page,1).'</code></pre>';
        //.'<pre><code>'.print_r($this->context_header(),1).'</code></pre>';
        //.'<pre><code>'.print_r($header,1).'</code></pre>';
        
    }
    
    public function course_authornames() {
        
        global $CFG, $USER, $DB, $OUTPUT, $COURSE;
        
        // expecting $course
        
        //$context = get_context_instance(CONTEXT_COURSE, $COURSE->id);
        $context = context_course::instance($COURSE->id);
        
        
        /// first find all roles that are supposed to be displayed
        if (!empty($CFG->coursecontact)) {
            $managerroles = explode(',', $CFG->coursecontact);
            $namesarray = array();
            $rusers = array();
            
            if (!isset($COURSE->managers)) {
                $rusers = get_role_users($managerroles, $context, true,
                                         'ra.id AS raid, u.id, u.username, u.firstname, u.lastname,
                                         u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename,
                                         r.name AS rolename, r.sortorder, r.id AS roleid',
                                         'r.sortorder ASC, u.lastname ASC');
            } else {
                //  use the managers array if we have it for perf reasosn
                //  populate the datastructure like output of get_role_users();
                foreach ($COURSE->managers as $manager) {
                    $u = new stdClass();
                    $u = $manager->user;
                    $u->roleid = $manager->roleid;
                    $u->rolename = $manager->rolename;
                    
                    $rusers[] = $u;
                }
            }
            
            /// Rename some of the role names if needed
            if (isset($context)) {
                $aliasnames = $DB->get_records('role_names', array('contextid'=>$context->id), '', 'roleid,contextid,name');
            }
            
            $namesarray = array();
            $canviewfullnames = has_capability('moodle/site:viewfullnames', $context);
            foreach ($rusers as $ra) {
                if (isset($namesarray[$ra->id])) {
                    //  only display a user once with the higest sortorder role
                    continue;
                }
                
                if (isset($aliasnames[$ra->roleid])) {
                    $ra->rolename = $aliasnames[$ra->roleid]->name;
                }
                
                $fullname = fullname($ra, $canviewfullnames);
                $usr_img = '<img class="instr-avatar img-rounded" style="border-radius: 0.25em" src="'.$CFG->wwwroot.'/user/pix.php/'.$ra->id.'/f2.jpg" height="24" width="24" title="Profile picture of '.$fullname.'" alt="Profile picture of '.$fullname.'" />';
                $namesarray[$ra->id] = html_writer::link(new moodle_url('/user/view.php', array('id'=>$ra->id, 'course'=>$COURSE->id)), $usr_img.' <span>'.$fullname.'</span>');
            }
            
            if (!empty($namesarray)) {
                $course_authornames = html_writer::start_tag('div', array('class'=>'teacherlist'));
                $course_authornames .= implode('', $namesarray);
                $course_authornames .= html_writer::end_tag('div');
                
                return $course_authornames;
            } else return '';
        }
    }
}
