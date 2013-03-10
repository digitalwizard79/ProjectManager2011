<?php

/**
 * Gateway object for psig_ProjectSignatures
 * 
 * @author Thomas Powers <digitalwizard79@gmail.com>
 * @copyright (c) 2011, Thomas Powers
 */
class ProjectSignatures extends Zend_Db_Table_Abstract
{
	protected $_name	= 'psig_ProjectSignatures';

	/**
	 * Add a signature
	 * 
	 * @param integer $pid
	 * @param integer $uid
	 * @param integer $section
	 * @return integer
	 */
	public function add($pid, $uid, $section)
	{
		$data	= array(
			'projectId'		=> $pid,
			'signatureId'	=> $uid,
			'sectionId'		=> $section,
		);

		return $this->insert($data);
	}

	/**
	 * Captures the signature
	 * 
	 * @param integer $pid
	 * @param integer $uid
	 * @param integer $section
	 */
	public function capture($pid, $uid, $section)
	{
		$dbAdapter			= $this->getAdapter();
		$data['captured'] 	= 1;
		$where[]			= $dbAdapter->quoteInto('projectId = ?', $pid);
		$where[]			= $dbAdapter->quoteInto('sectionId = ?', $section);
		$where[]			= $dbAdapter->quoteInto('signatureId = ?', $uid);

		$this->update($data, $where);

		$requiredSigs		= $this->fetchRequiredSignatures($pid, $section);
		$totalSigs			= count($requiredSigs);
		$capturedSigs		= 0;

		foreach($requiredSigs as $sig) {
			if( $sig->captured ) {
				$capturedSigs++;
			}
		}

		if($totalSigs == $capturedSigs) {
			$projects	= new Projects();
			$projects->moveForward($pid);
		}
	}

	/**
	 * Check if a signature exists
	 * 
	 * @param integer $pid
	 * @param integer $uid
	 * @param integer $section
	 * @return boolean
	 */
	public function exists($pid, $uid, $section)
	{
		$select	= $this->select()->where('projectId = ?', $pid)
								 ->where('sectionId = ?', $section)
								 ->where('signatureId = ?', $uid);

		$result	= $this->fetchAll($select);

		return (count($result) ? true : false);
	}

	/**
	 * Fetch list of required signatures for a project
	 * 
	 * @param integer $pid
	 * @param integer $section
	 * @return Zend_Db_Table_Rowset
	 */
	public function fetchRequiredSignatures($pid, $section = '')
	{
		$select	= $this->select()->from(array('psig' => $this->_name))
								 ->join(array('u' => 'u_Users'), 'u.id = psig.signatureId', array('name', 'email'))
								 ->join(array('ps' => 'ps_ProjectSections'), 'ps.id = psig.sectionId', array('sectionName' => 'name'))
								 ->order('psig.sectionId ASC')
								 ->where('psig.projectId = ?', $pid)
								 ->setIntegrityCheck(false);

		if($section != '') {
			$select->where('psig.sectionId = ?', $section);
		}

		return $this->fetchAll($select);
	}
}