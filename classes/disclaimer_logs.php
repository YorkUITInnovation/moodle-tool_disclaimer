<?php
/*
 * Author: Admin User
 * Create Date: 30-12-2024
 * License: LGPL 
 * 
 */
namespace tool_disclaimer;

class disclaimer_logs {

	/**
	 *
	 *@var string
	 */
	private $results;

	/**
	 *
	 *@global \moodle_database $DB
	 */
	public function __construct() {
	    global $DB;
	    $this->results = $DB->get_records('tool_disclaimer_log');
	}

	/**
	  * Get records
	 */
	public function get_records() {
	    return $this->results;
	}

	/**
	  * Array to be used for selects
	  * Defaults used key = record id, value = name 
	  * Modify as required. 
	 */
	public function get_select_array() {
	    $array = [
	        '' => get_string('select', 'tool_disclaimer')
	      ];
	      foreach($this->results as $r) {
	            $array[$r->id] = $r->name;
	      }
	    return $array;
	}

}