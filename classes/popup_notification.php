<?php
namespace tool_disclaimer;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/message/lib.php');

use renderable;
use templatable;
use moodle_url;
use core_user;

class popup_notification implements templatable, renderable {
    protected $notification;

    public function __construct($notification) {
        $this->notification = $notification;
    }

    public function export_for_template(\renderer_base $output) {
        $context = clone $this->notification;
        $context->timecreatedpretty = get_string('ago', 'message', format_time(time() - $context->timecreated));
        $context->text = message_format_message_text($context);
        $context->read = $context->timeread ? true : false;

        $context->subject = clean_param($context->subject, PARAM_TEXT);
        $context->contexturlname = clean_param($context->contexturlname, PARAM_TEXT);
        $context->shortenedsubject = shorten_text($context->subject, 125);

        $iconurl = $output->image_url('i/marker', 'core');
        $context->iconurl = $iconurl->out();

        return $context;
    }
}