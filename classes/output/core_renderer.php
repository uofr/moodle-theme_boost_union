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
 * @copyright  2022 Alexander Bias, lern.link GmbH <alexander.bias@lernlink.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_boost_union\output;
use context_system;
use context_course;
use moodle_url;

/**
 * Extending the core_renderer interface.
 *
 * @package    theme_boost_union
 * @copyright  2022 Alexander Bias, lern.link GmbH <alexander.bias@lernlink.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_renderer extends \theme_boost\output\core_renderer {

    /**
     * Returns the moodle_url for the favicon.
     *
     * This renderer function is copied and modified from /lib/outputrenderers.php
     *
     * It checks if the favicon is overridden in a flavour and, if yes, it serves this favicon.
     * If there isn't a favicon in any flavour set, it serves the general favicon.
     *
     * @since Moodle 2.5.1 2.6
     * @return moodle_url The moodle_url for the favicon
     * @throws \moodle_exception
     */
    public function favicon() {
        global $CFG;

        // Initialize static variable for the flavour favicon as this function might be called (for whatever reason) multiple times
        // during a page output.
        static $hasflavourfavicon, $flavourfaviconurl;

        // If the flavour favicon has already been checked.
        if ($hasflavourfavicon != null) {
            // If there is a flavour favicon.
            if ($hasflavourfavicon == true) {
                // Directly return the flavour favicon.
                return $flavourfaviconurl;
            }
            // Otherwise, if there isn't a flavour favicon, this function will continue to run the logic from Moodle core later.

            // Otherwise.
        } else {
            // Require flavours library.
            require_once($CFG->dirroot . '/theme/boost_union/flavours/flavourslib.php');

            // If any flavour applies to this page.
            $flavour = theme_boost_union_get_flavour_which_applies();
            if ($flavour != null) {
                // If the flavour has a favicon set.
                if ($flavour->look_favicon != null) {
                    // Remember this fact for subsequent runs of this function.
                    $hasflavourfavicon = true;

                    // Compose the URL to the flavour's favicon.
                    $flavourfaviconurl = moodle_url::make_pluginfile_url(
                            context_system::instance()->id, 'theme_boost_union', 'flavours_look_favicon', $flavour->id,
                            '/'.theme_get_revision(), '/'.$flavour->look_favicon);

                    // Return the URL.
                    return $flavourfaviconurl;

                    // Otherwise.
                } else {
                    // Remember this fact for subsequent runs of this function.
                    $hasflavourfavicon = false;
                }
            }
        }

        // Apparently, there isn't any flavour favicon set. Let's continue with the logic to serve the general favicon.
        $logo = null;
        if (!during_initial_install()) {
            $logo = get_config('theme_boost_union', 'favicon');
        }
        if (empty($logo)) {
            return $this->image_url('favicon', 'theme');
        }

        // Use $CFG->themerev to prevent browser caching when the file changes.
        return moodle_url::make_pluginfile_url(context_system::instance()->id, 'theme_boost_union', 'favicon', '64x64/',
                theme_get_revision(), $logo);
    }

    /**
     * Return the site's logo URL, if any.
     *
     * This renderer function is copied and modified from /lib/outputrenderers.php
     *
     * It checks if the logo is overridden in a flavour and, if yes, it serves this logo.
     * If there isn't a logo in any flavour set, it serves the general logo.
     *
     * @param int $maxwidth The maximum width, or null when the maximum width does not matter.
     * @param int $maxheight The maximum height, or null when the maximum height does not matter.
     * @return moodle_url|false
     */
    public function get_logo_url($maxwidth = null, $maxheight = 200) {
        global $CFG;

        // Initialize static variable for the flavour logo as this function might be called (for whatever reason) multiple times
        // during a page output.
        static $hasflavourlogo, $flavourlogourl;

        // If the flavour logo has already been checked.
        if ($hasflavourlogo != null) {
            // If there is a flavour logo.
            if ($hasflavourlogo == true) {
                // Directly return the flavour logo.
                return $flavourlogourl;
            }
            // Otherwise, if there isn't a flavour logo, this function will continue to run the logic from Moodle core later.

            // Otherwise.
        } else {
            // Require flavours library.
            require_once($CFG->dirroot . '/theme/boost_union/flavours/flavourslib.php');

            // If any flavour applies to this page.
            $flavour = theme_boost_union_get_flavour_which_applies();
            if ($flavour != null) {
                // If the flavour has a logo set.
                if ($flavour->look_logo != null) {
                    // Remember this fact for subsequent runs of this function.
                    $hasflavourlogo = true;

                    // Compose the URL to the flavour's logo.
                    $flavourlogourl = moodle_url::make_pluginfile_url(
                            context_system::instance()->id, 'theme_boost_union', 'flavours_look_logo', $flavour->id,
                            '/'.theme_get_revision(), '/'.$flavour->look_logo);

                    // Return the URL.
                    return $flavourlogourl;

                    // Otherwise.
                } else {
                    // Remember this fact for subsequent runs of this function.
                    $hasflavourlogo = false;
                }
            }
        }

        // Apparently, there isn't any flavour logo set. Let's continue to serve the general logo.
        $logo = get_config('theme_boost_union', 'logo');
        if (empty($logo)) {
            return false;
        }

        // If the logo is a SVG image, do not add a size to the path.
        $logoextension = pathinfo($logo, PATHINFO_EXTENSION);
        if (in_array($logoextension, ['svg', 'svgz'])) {
            // The theme_boost_union_pluginfile() function will look for a filepath and will try to extract the size from that.
            // Thus, we cannot drop the filepath from the URL completely.
            // But we can add a path without an 'x' in it which will then be interpreted by theme_boost_union_pluginfile()
            // as "no resize requested".
            $filepath = '1/';

            // Otherwise, add a size to the path.
        } else {
            // 200px high is the default image size which should be displayed at 100px in the page to account for retina displays.
            // It's not worth the overhead of detecting and serving 2 different images based on the device.

            // Hide the requested size in the file path.
            $filepath = ((int) $maxwidth . 'x' . (int) $maxheight) . '/';
        }

        // Use $CFG->themerev to prevent browser caching when the file changes.
        return moodle_url::make_pluginfile_url(context_system::instance()->id, 'theme_boost_union', 'logo', $filepath,
                theme_get_revision(), $logo);
    }

    /**
     * Return the site's compact logo URL, if any.
     *
     * This renderer function is copied and modified from /lib/outputrenderers.php
     *
     * It checks if the logo is overridden in a flavour and, if yes, it serves this logo.
     * If there isn't a logo in any flavour set, it serves the general compact logo.
     *
     * @param int $maxwidth The maximum width, or null when the maximum width does not matter.
     * @param int $maxheight The maximum height, or null when the maximum height does not matter.
     * @return moodle_url|false
     */
    public function get_compact_logo_url($maxwidth = 300, $maxheight = 300) {
        global $CFG,$OUTPUT;

        // Initialize static variable for the flavour logo as this function is called (for whatever reason) multiple times
        // during a page output.
        static $hasflavourlogo, $flavourlogourl;

        // If the flavour logo has already been checked.
        if ($hasflavourlogo != null) {
            // If there is a flavour logo.
            if ($hasflavourlogo == true) {
                // Directly return the flavour logo.
                return $flavourlogourl;
            }
            // Otherwise, if there isn't a flavour logo, this function will continue to run the logic from Moodle core later.

            // Otherwise.
        } else {
            // Require flavours library.
            require_once($CFG->dirroot . '/theme/boost_union/flavours/flavourslib.php');

            // If any flavour applies to this page.
            $flavour = theme_boost_union_get_flavour_which_applies();
            if ($flavour != null) {
                // If the flavour has a compact logo set.
                if ($flavour->look_logocompact != null) {
                    // Remember this fact for subsequent runs of this function.
                    $hasflavourlogo = true;

                    // Compose the URL to the flavour's compact logo.
                    $flavourlogourl = moodle_url::make_pluginfile_url(
                            context_system::instance()->id, 'theme_boost_union', 'flavours_look_logocompact', $flavour->id,
                            '/'.theme_get_revision(), '/'.$flavour->look_logocompact);

                    // Return the URL.
                    return $flavourlogourl;

                    // Otherwise.
                } else {
                    // Remember this fact for subsequent runs of this function.
                    $hasflavourlogo = false;
                }
            }
        }

        // Apparently, there isn't any flavour logo set. Let's continue to service the general compact logo.
        $logo = get_config('theme_boost_union', 'logocompact');
        if (empty($logo)) {
            return false;
        }

        // If the logo is a SVG image, do not add a size to the path.
        $logoextension = pathinfo($logo, PATHINFO_EXTENSION);
        if (in_array($logoextension, ['svg', 'svgz'])) {
            // The theme_boost_union_pluginfile() function will look for a filepath and will try to extract the size from that.
            // Thus, we cannot drop the filepath from the URL completely.
            // But we can add a path without an 'x' in it which will then be interpreted by theme_boost_union_pluginfile()
            // as "no resize requested".
            $filepath = '1/';

            // Otherwise, add a size to the path.
        } else {
            // Hide the requested size in the file path.
            $filepath = ((int)$maxwidth . 'x' . (int)$maxheight) . '/';
        }

        // Use $CFG->themerev to prevent browser caching when the file changes.
		if (current_language()=='en') {
			$logo_url = moodle_url::make_pluginfile_url(context_system::instance()->id, 'theme_boost_union', 'logocompact', $filepath,
                theme_get_revision(), $logo);
			
		} else {
			$logo_url = $OUTPUT->image_url('Imagineur-logo-with-tagline-white_fr', 'theme');
		}
		
        return $logo_url;
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

        // Require local libraries.
        require_once($CFG->dirroot . '/theme/boost_union/locallib.php');
        require_once($CFG->dirroot . '/theme/boost_union/flavours/flavourslib.php');

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

        // If there is a flavour applied to this page, add the flavour ID as additional body class.
        // Boost Union itself does not need this class for applying the flavour to the page (yet).
        // However, theme designers might want to use it.
        $flavour = theme_boost_union_get_flavour_which_applies();
        if ($flavour != null) {
            $additionalclasses[] = 'flavour'.'-'.$flavour->id;
        }

        return ' id="'. $this->body_id().'" class="'.$this->body_css_classes($additionalclasses).'"';
    }

    /**
     * Wrapper for header elements.
     *
     * This renderer function is copied and modified from /lib/outputrenderers.php
     *
     * @return string HTML to display the main header.
     */
    public function full_header() {
        $pagetype = $this->page->pagetype;
        $homepage = get_home_page();
        $homepagetype = null;
        // Add a special case since /my/courses is a part of the /my subsystem.
        if ($homepage == HOMEPAGE_MY || $homepage == HOMEPAGE_MYCOURSES) {
            $homepagetype = 'my-index';
        } else if ($homepage == HOMEPAGE_SITE) {
            $homepagetype = 'site-index';
        }
        if ($this->page->include_region_main_settings_in_header_actions() &&
                !$this->page->blocks->is_block_present('settings')) {
            // Only include the region main settings if the page has requested it and it doesn't already have
            // the settings block on it. The region main settings are included in the settings block and
            // duplicating the content causes behat failures.
            $this->page->add_header_action(html_writer::div(
                    $this->region_main_settings_menu(),
                    'd-print-none',
                    ['id' => 'region-main-settings-menu']
            ));
        }

        $header = new \stdClass();
        $header->settingsmenu = $this->context_header_settings_menu();
        $header->contextheader = $this->context_header();
        $header->hasnavbar = empty($this->page->layout_options['nonavbar']);
        $header->navbar = $this->navbar();
        $header->pageheadingbutton = $this->page_heading_button();
        $header->courseheader = $this->course_header();
        $header->headeractions = $this->page->get_header_actions();

        // Add the course header image for rendering.
        if ($this->page->pagelayout == 'course' && (get_config('theme_boost_union', 'courseheaderimageenabled')
                        == THEME_BOOST_UNION_SETTING_SELECT_YES)) {
            // If course header images are activated, we get the course header image url
            // (which might be the fallback image depending on the course settings and theme settings).
            $header->courseheaderimageurl = theme_boost_union_get_course_header_image_url();
            // Additionally, get the course header image height.
            $header->courseheaderimageheight = get_config('theme_boost_union', 'courseheaderimageheight');
            // Additionally, get the course header image position.
            $header->courseheaderimageposition = get_config('theme_boost_union', 'courseheaderimageposition');
            // Additionally, get the template context attributes for the course header image layout.
            $courseheaderimagelayout = get_config('theme_boost_union', 'courseheaderimagelayout');
            switch($courseheaderimagelayout) {
                case THEME_BOOST_UNION_SETTING_COURSEIMAGELAYOUT_HEADINGABOVE:
                    $header->courseheaderimagelayoutheadingabove = true;
                    $header->courseheaderimagelayoutstackedclass = '';
                    break;
                case THEME_BOOST_UNION_SETTING_COURSEIMAGELAYOUT_STACKEDDARK:
                    $header->courseheaderimagelayoutheadingabove = false;
                    $header->courseheaderimagelayoutstackedclass = 'dark';
                    break;
                case THEME_BOOST_UNION_SETTING_COURSEIMAGELAYOUT_STACKEDLIGHT:
                    $header->courseheaderimagelayoutheadingabove = false;
                    $header->courseheaderimagelayoutstackedclass = 'light';
                    break;
            }
        }

        if (!empty($pagetype) && !empty($homepagetype) && $pagetype == $homepagetype) {
            $header->welcomemessage = \core_user::welcome_message();
        }
        return $this->render_from_template('core/full_header', $header);
    }

    /**
     * Renders the "breadcrumb" for all pages in boost union.
     *
     * This renderer function is copied and modified from /theme/boost/classes/output/core_renderer.php
     *
     * @return string the HTML for the navbar.
     */
    public function navbar(): string {
        $newnav = new \theme_boost_union\boostnavbar($this->page);
        return $this->render_from_template('core/navbar', $newnav);
    }
	
	
	public function profile_prompt() {
		global $CFG, $PAGE, $USER, $COURSE;
		$lang = \current_language();
		
		$currurl = $PAGE->url;
		
		if ($USER->id>0) profile_load_custom_fields($USER);
		
		$context = context_course::instance($COURSE->id);
		
		$profile_prompt = '';//'course:'.$COURSE->id.' hascap:'.has_capability('moodle/course:update', $context).' || '.$currurl;
		
		$shouldprompt = false;
		
		if (empty($USER->profile['residence'])) $shouldprompt = true;
		if (empty($USER->profile['gender'])) $shouldprompt = true;
		if (empty($USER->profile['ethnicity'])) $shouldprompt = true;
		if (empty($USER->profile['occupation'])) $shouldprompt = true;
		
		if ($USER->id>0 && !has_capability('moodle/course:update', $context)) {
			if ($shouldprompt && !str_contains($currurl, 'user/edit.php')) {
				$profile_prompt .= '<script>
		window.addEventListener(\'load\', function() { 
		
			var modalmarkup = \'<!-- Modal -->\';
			modalmarkup += \'<div class="modal fade" id="lightboxmodal" role="dialog">\';
			modalmarkup += \'<div class="modal-dialog" id="lbdialog">\';
			modalmarkup += \'  <!-- Modal content-->\';
			modalmarkup += \'    <div class="modal-content">\';
			modalmarkup += \'      <div class="modal-header">\';
			//modalmarkup += \'        <button type="button" class="close" data-dismiss="modal">&times;</button>\';
			modalmarkup += \'        <h4 class="modal-title">Help improve our data!</h4>\';
			modalmarkup += \'      </div>\';
			modalmarkup += \'      <div class="modal-body">\';
			modalmarkup += \'        <p>Take a moment to update your profile to ensure accurate reporting to our funder.  Your personal information remains anonymous and secure.</p>  <div class="alert alert-info"><p>Please note: the first and last name that appears in your profile is what will appear on your digital badge.</p></div>\';
			modalmarkup += \'      </div>\';
			modalmarkup += \'      <div class="modal-footer">\';
			modalmarkup += \'        <button type="button" class="btn btn-primary" onclick="location.href=\\\''.$CFG->wwwroot.'/user/edit.php?id='.$USER->id.'\\\'">Update Profile</button>\';
			modalmarkup += \'      </div>\';
			modalmarkup += \'    </div>\';
			modalmarkup += \' </div>\';
			modalmarkup += \'</div>\';

			$("body").append(modalmarkup);
			//$("#lightboxmodal").modal(\'show\');
			$("#lightboxmodal").modal({backdrop: \'static\',show: true});
			//console.log(\'pop modal\');
			
		});
	    </script>';
			}/* else if (str_contains($currurl, 'user/edit.php')) {
				$profile_prompt .= '<code><pre>Profile '.print_r($USER->profile,1).'</pre></code>';
			}*/
		}
		
		
		
		//ensure on the right pages,a nd shown only to students
		//has_capability('theme/boost_union:editregion'.$regioncapname, $this->page->context);
		
		//profile_load_custom_fields($USER);
		
		
		
		
		//$langlink .= print_r($currurl,1);
		
		return $profile_prompt;
	}
	
	
	public function nav_language() {
		global $CFG, $PAGE;
		$lang = \current_language();
		
		$currurl = $PAGE->url;
		
		if ($lang == 'en') {
			$currurl->param('lang','fr_ca');
			$langlink = '<a href="'.$currurl->out().'">Français</a>';
		} else {
			$currurl->param('lang','en');
			$langlink = '<a href="'.$currurl->out().'">English</a>';
		}
		
		//$langlink .= print_r($currurl,1);
		
		return $langlink;
	}
	
	public function logo_src() {
		global $OUTPUT;
		return $OUTPUT->image_url('University-of-Regina-Hill-Levene-Schools-of-Business-Logo', 'theme');
	}
	
	public function logo_alt() {
		$lang = \current_language();
		if ($lang == 'en') {
			$alt = 'University of Regina - Hill Levene Schools of Business - Visit site';
		} else {
			$alt = 'Université de Regina - écoles de commerce Hill Levene - Visiter le site';
		}
		
		return $alt;
	}
	
	public function funding_logo_src() {
		global $OUTPUT;
		return $OUTPUT->image_url('canada-wordmark-colour', 'theme');
	}
	
	public function funding_statement() {
		$lang = \current_language();
		if ($lang == 'en') {
			$statement = 'Funded by the Government of Canada’s Skills for Success Program';
		} else {
			$statement = 'Financé par le programme Compétences pour réussir du gouvernement du Canada';
		}
		
		return $statement;
	}
	
	public function login_status() {
		global $CFG;
		//$lang = \current_language();
		
		if (isloggedin()) {
			$lslink = '<a href="'.$CFG->wwwroot.'/login/logout.php?sesskey='.sesskey().'">'.get_string('logout').'</a>';
		} else {
			$lslink = '<a href="'.$CFG->wwwroot.'/login/index.php">'.get_string('login').'</a>';
		}
		/*
		if ($lang == 'en') {
			
			
		} else {
			if (isloggedin()) {
				$lslink = '<a href="'.$CFG->wwwroot.'/login/logout.php?sesskey='.sesskey().'">Log out</a>';
			} else {
				$lslink = '<a href="'.$CFG->wwwroot.'/login/index.php">Log in</a>';
			}
		}
		*/
		
		return $lslink;
	}
	
	public function contact_email() {
		global $CFG;
		$lang = \current_language();
		
		$contact_str = ($lang == 'fr_ca') ? 'Contactez-nous' : 'Contact Us';
		
		$contact_link = '<a href="mailto:'.$CFG->noreplyaddress.'">'.$contact_str.'</a>';
		
		return $contact_link;
	}
	
    /**
     * Prints a nice side block with an optional header.
     *
     * This renderer function is copied and modified from /lib/outputrenderers.php
     *
     * @param block_contents $bc HTML for the content
     * @param string $region the region the block is appearing in.
     * @return string the HTML to be output.
     */
    public function block(\block_contents $bc, $region) {
        global $CFG;

        // Require own locallib.php.
        require_once($CFG->dirroot . '/theme/boost_union/locallib.php');

        $bc = clone($bc); // Avoid messing up the object passed in.
        if (empty($bc->blockinstanceid) || !strip_tags($bc->title)) {
            $bc->collapsible = \block_contents::NOT_HIDEABLE;
        }

        $id = !empty($bc->attributes['id']) ? $bc->attributes['id'] : uniqid('block-');
        $context = new \stdClass();
        $context->skipid = $bc->skipid;
        $context->blockinstanceid = $bc->blockinstanceid ?: uniqid('fakeid-');
        $context->dockable = $bc->dockable;
        $context->id = $id;
        $context->hidden = $bc->collapsible == \block_contents::HIDDEN;
        $context->skiptitle = strip_tags($bc->title);
        $context->showskiplink = !empty($context->skiptitle);
        $context->arialabel = $bc->arialabel;
        $context->ariarole = !empty($bc->attributes['role']) ? $bc->attributes['role'] : 'complementary';
        $context->class = $bc->attributes['class'];
        $context->type = $bc->attributes['data-block'];
        $context->title = $bc->title;
        $context->content = $bc->content;
        $context->annotation = $bc->annotation;
        $context->footer = $bc->footer;
        $context->hascontrols = !empty($bc->controls);

        // Hide edit control options for the regions based on the capabilities.
        $regions = theme_boost_union_get_additional_regions();
        $regioncapname = array_search($region, $regions);
        if (!empty($regioncapname) && $context->hascontrols) {
            $context->hascontrols = has_capability('theme/boost_union:editregion'.$regioncapname, $this->page->context);
        }

        if ($context->hascontrols) {
            $context->controls = $this->block_controls($bc->controls, $id);
        }

        return $this->render_from_template('core/block', $context);
    }
}
