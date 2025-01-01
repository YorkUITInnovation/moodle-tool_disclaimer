<?php
/*
 * Author: Admin User
 * Create Date: 30-12-2024
 * License: LGPL 
 * 
 */
namespace tool_disclaimer;

use tool_disclaimer\crud;

class disclaimer_log extends crud {


	/**
	 *
	 *@var int
	 */
	private $id;

	/**
	 *
	 *@var int
	 */
	private $disclaimerid;

	/**
	 *
	 *@var int
	 */
	private $userid;

    /**
     * @var int
     */
    private $objectid;

	/**
	 *
	 *@var int
	 */
	private $response;

	/**
	 *
	 *@var int
	 */
	private $attempt;

	/**
	 *
	 *@var int
	 */
	private $usermodified;

	/**
	 *
	 *@var int
	 */
	private $timecreated;

	/**
	 *
	 *@var string
	 */
	private $timecreated_hr;

	/**
	 *
	 *@var int
	 */
	private $timemodified;

	/**
	 *
	 *@var string
	 */
	private $timemodified_hr;

	/**
	 *
	 *@var string
	 */
	private $table;


    /**
     *  
     *
     */
	public function __construct($id = 0){
  	global $CFG, $DB, $DB;

		$this->table = 'tool_disclaimer_log';

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

		$this->disclaimerid = $result->disclaimerid ?? 0;
		$this->userid = $result->userid ?? 0;
        $this->objectid = $result->objectid ?? 0;
		$this->response = $result->response ?? 0;
		$this->attempt = $result->attempt ?? 0;
		$this->usermodified = $result->usermodified ?? 0;
		$this->timecreated = $result->timecreated ?? 0;
          $this->timecreated_hr = '';
          if ($this->timecreated) {
		        $this->timecreated_hr = userdate($result->timecreated,get_string('strftimedate'));
          }
		$this->timemodified = $result->timemodified ?? 0;
      $this->timemodified_hr = '';
          if ($this->timemodified) {
		        $this->timemodified_hr = userdate($result->timemodified,get_string('strftimedate'));
          }
	}

	/**
	 * @return id - bigint (18)
	 */
	public function get_id(){
		return $this->id;
	}

	/**
	 * @return disclaimerid - bigint (18)
	 */
	public function get_disclaimerid(){
		return $this->disclaimerid;
	}

	/**
	 * @return userid - bigint (18)
	 */
	public function get_userid(){
		return $this->userid;
	}

    /**
     * @return objectid - bigint (18)
     */
    public function get_objectid(){
        return $this->objectid;
    }

	/**
	 * @return response - tinyint (2)
	 */
	public function get_response(){
		return $this->response;
	}

	/**
	 * @return attempt - bigint (18)
	 */
	public function get_attempt(){
		return $this->attempt;
	}

	/**
	 * @return usermodified - bigint (18)
	 */
	public function get_usermodified(){
		return $this->usermodified;
	}

	/**
	 * @return timecreated - bigint (18)
	 */
	public function get_timecreated(){
		return $this->timecreated;
	}

	/**
	 * @return timemodified - bigint (18)
	 */
	public function get_timemodified(){
		return $this->timemodified;
	}

	/**
	 * @param Type: bigint (18)
	 */
	public function set_id($id){
		$this->id = $id;
	}

	/**
	 * @param Type: bigint (18)
	 */
	public function set_disclaimerid($disclaimerid){
		$this->disclaimerid = $disclaimerid;
	}

	/**
	 * @param Type: bigint (18)
	 */
	public function set_userid($userid){
		$this->userid = $userid;
	}

	/**
	 * @param Type: tinyint (2)
	 */
	public function set_response($response){
		$this->response = $response;
	}

	/**
	 * @param Type: bigint (18)
	 */
	public function set_attempt($attempt){
		$this->attempt = $attempt;
	}

	/**
	 * @param Type: bigint (18)
	 */
	public function set_usermodified($usermodified){
		$this->usermodified = $usermodified;
	}

	/**
	 * @param Type: bigint (18)
	 */
	public function set_timecreated($timecreated){
		$this->timecreated = $timecreated;
	}

	/**
	 * @param Type: bigint (18)
	 */
	public function set_timemodified($timemodified){
		$this->timemodified = $timemodified;
	}

}