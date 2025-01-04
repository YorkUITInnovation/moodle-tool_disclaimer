<?php
/*
 * Author: Admin User
 * Create Date: 30-12-2024
 * License: LGPL 
 * 
 */

namespace tool_disclaimer;

use tool_disclaimer\crud;

class disclaimer extends crud
{


    /**
     *
     * @var int
     */
    private $id;

    /**
     *
     * @var string
     */
    private $name;

    /**
     *
     * @var string
     */
    private $context;

    /**
     *
     * @var string
     */
    private $contextpath;

    /**
     *
     * @var int
     */
    private $frontpageonly;

    /**
     *
     * @var string
     */
    private $subject;

    /**
     *
     * @var string
     */
    private $message;

    /**
     *
     * @var string
     */
    private $messageformat;

    /**
     *
     * @var int
     */
    private $usepublisheddate;

    /**
     *
     * @var int
     */
    private $published;

    /**
     * @var int
     */
    private $publishedstart;

    /**
     * @var int
     */
    private $publishedend;

    /**
     * @var string
     */
    private $redirectto;

    /**
     *
     * @var string
     */
    private $categories;

    /**
     *
     * @var string
     */
    private $courses;

    /**
     *
     * @var int
     */
    private $usermodified;

    /**
     *
     * @var int
     */
    private $timecreated;

    /**
     *
     * @var string
     */
    private $timecreated_hr;

    /**
     *
     * @var int
     */
    private $timemodified;

    /**
     *
     * @var string
     */
    private $timemodified_hr;

    /**
     *
     * @var string
     */
    private $table;


    /**
     *
     *
     */
    public function __construct($id = 0)
    {
        global $CFG, $DB, $DB;

        $this->table = 'tool_disclaimer';

        parent::set_table($this->table);

        if ($id) {
            $this->id = $id;
            parent::set_id($this->id);
            $result = $this->get_record($this->table, $this->id);
        } else {
            $result = new \stdClass();
            $this->id = 0;
            parent::set_id($this->id);
        }

        $this->name = $result->name ?? '';
        $this->context = $result->context ?? '';
        $this->frontpageonly = $result->frontpageonly ?? 0;
        $this->subject = $result->subject ?? '';
        $this->message = $result->message ?? '';
        $this->messageformat = $result->messageformat ?? '';
        $this->usepublisheddate = $result->usepublisheddate ?? 0;
        $this->published = $result->published ?? 0;
        $this->publishedstart = $result->publishedstart ?? 0;
        $this->publishedend = $result->publishedend ?? 0;
        $this->redirectto = $result->redirectto ?? '';
        $this->categories = $result->categories ?? '';
        $this->courses = $result->courses ?? '';
        $this->usermodified = $result->usermodified ?? 0;
        $this->timecreated = $result->timecreated ?? 0;
        $this->timecreated_hr = '';
        if ($this->timecreated) {
            $this->timecreated_hr = userdate($result->timecreated, get_string('strftimedate'));
        }
        $this->timemodified = $result->timemodified ?? 0;
        $this->timemodified_hr = '';
        if ($this->timemodified) {
            $this->timemodified_hr = userdate($result->timemodified, get_string('strftimedate'));
        }
    }

    /**
     * Insert record and roles
     * @param $data
     */
    public function insert_record($data)
    {
        global $DB, $USER;

        // If usepublisheddate is set to no, set publishedstart and publishedend to 0
        if ($data->usepublisheddate == 0) {
            $data->publishedstart = 0;
            $data->publishedend = 0;
        } else {
            // Set published to 0 if usepublisheddate is set to yes
            $data->published = 0;
        }

        $disclaimer_id = parent::insert_record($data);

        // Add roles
        $roles = $data->roles;
        foreach ($roles as $key => $value) {
            $role = new \stdClass();
            $role->disclaimerid = $disclaimer_id;
            $role->role = $value;
            $role->timecreated = time();
            $role->timemodified = time();
            $role->usermodified = $USER->id;
            $DB->insert_record('tool_disclaimer_role', $role);
        }

        return $disclaimer_id;
    }

    /**
     * Update record and roles
     * @param $data
     */
    public function update_record($data) {
        global $DB, $USER;

        // If usepublisheddate is set to no, set publishedstart and publishedend to 0
        if ($data->usepublisheddate == 0) {
            $data->publishedstart = 0;
            $data->publishedend = 0;
        } else {
            // Set published to 0 if usepublisheddate is set to yes
            $data->published = 0;
        }

        $disclaimer_id = parent::update_record($data);

        // Delete existing roles
        $DB->delete_records('tool_disclaimer_role', ['disclaimerid' => $disclaimer_id]);

        // Add roles
        $roles = $data->roles;
        foreach ($roles as $key => $value) {
            $role = new \stdClass();
            $role->disclaimerid = $disclaimer_id;
            $role->role = $value;
            $role->timecreated = time();
            $role->timemodified = time();
            $role->usermodified = $USER->id;
            $DB->insert_record('tool_disclaimer_role', $role);
        }

        return $disclaimer_id;
    }

    /**
     * @return id - bigint (18)
     */
    public function get_id()
    {
        return $this->id;
    }

    /**
     * @return name - varchar (255)
     */
    public function get_name()
    {
        return $this->name;
    }

    /**
     * @return context - varchar (25)
     */
    public function get_context()
    {
        return $this->context;
    }

    /**
     * @return array
     * @throws \dml_exception
     */
    public function get_roles()
    {
        global $DB;
        $sql = "SELECT * FROM {tool_disclaimer_role} WHERE disclaimerid = ?";
        $disclaimer_roles = $DB->get_records_sql($sql, array($this->id));
        $roles = [];
        foreach($disclaimer_roles as $role) {
            $roles[] = $role->role;
        }
        return $roles;
    }
    /**
     * @return excludesite - bool
     */
    public function get_frontpageonly(): bool
    {
        if ($this->frontpageonly) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return subject - varchar (255)
     */
    public function get_subject()
    {
        return $this->subject;
    }

    /**
     * @return message - longtext (-1)
     */
    public function get_message()
    {
        return $this->message;
    }

    /**
     * @return messageformat - varchar (255)
     */
    public function get_messageformat()
    {
        return $this->messageformat;
    }

    /**
     * @return usepublisheddate - bool
     */
    public function get_usepublisheddate(): bool
    {
        if ($this->usepublisheddate) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return published - bool
     */
    public function get_published(): bool
    {
        if ($this->published) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return int
     */
    public function get_pubishedstart(): int
    {
        return $this->publishedstart;
    }

    /**
     * @return int
     */
    public function get_publishedend(): int
    {
        return $this->publishedend;
    }

    /**
     * @return string
     */
    public function get_redirectto(): string
    {
        return $this->redirectto;
    }

    /**
     * @return categories - longtext (-1)
     */
    public function get_categories()
    {
        return $this->categories;
    }

    /**
     * @return courses - longtext (-1)
     */
    public function get_courses()
    {
        return $this->courses;
    }

    /**
     * @return usermodified - bigint (18)
     */
    public function get_usermodified()
    {
        return $this->usermodified;
    }

    /**
     * @return timecreated - bigint (18)
     */
    public function get_timecreated()
    {
        return $this->timecreated;
    }

    /**
     * @return timemodified - bigint (18)
     */
    public function get_timemodified()
    {
        return $this->timemodified;
    }

    /**
     * @param Type: bigint (18)
     */
    public function set_id($id)
    {
        $this->id = $id;
    }

    /**
     * @param Type: varchar (255)
     */
    public function set_name($name)
    {
        $this->name = $name;
    }

    /**
     * @param Type: varchar (25)
     */
    public function set_context($context)
    {
        $this->context = $context;
    }

    /**
     * @param Type: tinyint (2)
     */
    public function set_frontpageonly($frontpageonly)
    {
        $this->frontpageonly = $frontpageonly;
    }

    /**
     * @param Type: varchar (255)
     */
    public function set_subject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @param Type: longtext (-1)
     */
    public function set_message($message)
    {
        $this->message = $message;
    }

    /**
     * @param Type: tinyint (1)
     */
    public function set_usepublisheddate($usepublisheddate)
    {
        $this->usepublisheddate = $usepublisheddate;
    }

    /**
     * @param Type: tinyint (2)
     */
    public function set_published($published)
    {
        $this->published = $published;
    }

    /**
     * @param Type: longtext (-1)
     */
    public function set_categories($categories)
    {
        $this->categories = $categories;
    }

    /**
     * @param Type: longtext (-1)
     */
    public function set_courses($courses)
    {
        $this->courses = $courses;
    }

    /**
     * @param Type: bigint (18)
     */
    public function set_usermodified($usermodified)
    {
        $this->usermodified = $usermodified;
    }

    /**
     * @param Type: bigint (18)
     */
    public function set_timecreated($timecreated)
    {
        $this->timecreated = $timecreated;
    }

    /**
     * @param Type: bigint (18)
     */
    public function set_timemodified($timemodified)
    {
        $this->timemodified = $timemodified;
    }

}