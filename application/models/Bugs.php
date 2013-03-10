<?php

/**
 * Provides a gateway object for b_Bugs
 * 
 * @author Thomas Powers <digitalwizard79@gmail.com>
 * @copyright (c) 2011, Thomas Powers
 */
class Bugs extends Zend_Db_Table_Abstract
{
	/**
	 * Name of the table
	 * @var string 
	 */
	protected $_name = 'b_Bugs';

	/**
	 * Insert data into the table
	 * 
	 * @param array $data
	 * @param integer $pid
	 * @param integer $section
	 */
	public function add(array $data, $pid, $section)
	{
		$data['projectId']	= $pid;
		$data['reporter']	= Zend_Auth::getInstance()->getIdentity()->id;
		$data['sectionId']	= $section;

		$this->insert($data);
	}

	/**
	 * Fetch all data for a project
	 * 
	 * @param integer $pid
	 * @param integer $section
	 * @return Zend_Db_Table_Rowset
	 */
	public function fetchAllByProject($pid, $section)
	{
		$select	= $this->select()->from(array('b' => $this->_name))
								 ->join(array('bc' => 'bc_BugCategories'), 'b.categoryId = bc.id', array('categoryName' => 'name'))
								 ->join(array('bs' => 'bs_BugSeverity'), 'b.severityId = bs.id', array('severityName' => 'name', 'severityWeight' => 'weight'))
								 ->join(array('bp' => 'bp_BugPriority'), 'b.priorityId = bp.id', array('priorityName' => 'name', 'priorityWeight' => 'weight'))
								 ->join(array('u' => 'u_Users'), 'b.reporter = u.id', array('reporterName' => 'name', 'reporterEmail' => 'email'))
								 ->join(array('u2' => 'u_Users'), 'b.assignedTo = u2.id', array('assignedName' => 'name', 'assignedEmail' => 'email'))
								 ->where('b.projectId = ?', $pid)
								 ->where('b.sectionId = ?', $section)
								 ->setIntegrityCheck(false);

		return $this->fetchAll($select);
	}

	/**
	 * Fetch an existing bug
	 * 
	 * @param integer $bid
	 * @return Zend_Db_Table_Row
	 */
	public function fetchBug($bid)
	{
		$select	= $this->select()->from(array('b' => $this->_name))
								 ->join(array('bc' => 'bc_BugCategories'), 'b.categoryId = bc.id', array('categoryName' => 'name'))
								 ->join(array('bs' => 'bs_BugSeverity'), 'b.severityId = bs.id', array('severityName' => 'name', 'severityWeight' => 'weight'))
								 ->join(array('bp' => 'bp_BugPriority'), 'b.priorityId = bp.id', array('priorityName' => 'name', 'priorityWeight' => 'weight'))
								 ->join(array('u' => 'u_Users'), 'b.reporter = u.id', array('reporterName' => 'name', 'reporterEmail' => 'email'))
								 ->join(array('u2' => 'u_Users'), 'b.assignedTo = u2.id', array('assignedName' => 'name', 'assignedEmail' => 'email'))
								 ->where('b.id = ?', $bid)
								 ->setIntegrityCheck(false);

		return $this->fetchRow($select);
	}

	/**
	 * Fetch all categories
	 * 
	 * @return Zend_Db_Table_Rowset
	 */
	public function fetchCategories()
	{
		$bc	= new BugCategories();
		return $bc->fetchAll();
	}

	/**
	 * Fetch the notes for a specific bug
	 * 
	 * @return Zend_Db_Table_Rowset
	 */
	public function fetchNotes($bid)
	{
		$bn	= new BugNotes();
		return $bn->fetchNotes($bid);
	}

	/**
	 * Fetch priorities
	 * 
	 * @return Zend_Db_Table_Rowset
	 */
	public function fetchPriorities()
	{
		$bc	= new BugPriority();
		return $bc->fetchAll();
	}

	/**
	 * Fetch severities
	 * 
	 * @return Zend_Db_Table_Rowset
	 */
	public function fetchSeverities()
	{
		$bc	= new BugSeverity();
		return $bc->fetchAll();
	}

	/**
	 * Update the status of an existing bug
	 * 
	 * @param string $newStatus
	 * @param integer $bid
	 */
	public function updateStatus($newStatus, $bid)
	{
		$this->update(array('status' => $newStatus), 'id = '. $bid);
	}
}