<?php
/*
 * Author: Admin User
 * Create Date: 30-12-2024
 * License: LGPL 
 * 
 */
namespace tool_disclaimer;

abstract class crud {


/**
/* string
**/
private $table;

/**
/* int
**/
private $id;

    /**
     * Get record
     *
     * @global \moodle_database $DB
     * 
     */
	public function get_record(){
	    global $DB;
	    $result = $DB->get_record($this->table, ['id' => $this->id]);
	    return  $result;

	}

    /**
     * Delete the row 
     *
     * @global \moodle_database $DB
     *
     */
	public function delete_record(){
	    global $DB;
		$DB->delete_records($this->table,['id' => $this->id]);
	}

    /**
     * Insert record into selected table
     * @global \moodle_database $DB
     * @global \stdClass $USER
     * @param object $data
     */
	public function insert_record($data){
		global $DB, $USER;

		if (!isset($data->timecreated)) {
		    $data->timecreated = time();
		}

		if (!isset($data->imemodified)) {
		    $data->timemodified = time();
		}

		//Set user
		$data->usermodified = $USER->id;

		$id = $DB->insert_record($this->table, $data);

		return $id;
	}

    /**
     * Update record into selected table
     * @global \moodle_database $DB
     * @global \stdClass $USER
     * @param object $data
     */
	public function update_record($data){
		global $DB, $USER;

		if (!isset($data->timemodified)) {
		    $data->timemodified = time();
		}

		//Set user
		$data->usermodified = $USER->id;

		$id = $DB->update_record($this->table, $data);

		return $id;
	}

/**
/* get id
**/
public function get_id() {
  return $this->id;
}

/**
/* get table
**/
public function get_table() {
  return $this->table;
}

public function set_id($id)
{
    $this->id = $id;
}

public function set_table($table)
{
    $this->table = $table;
}

}