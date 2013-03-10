<?php

/**
 * Gateway object for up_UserProjects
 * 
 * @author Thomas Powers <digitalwizard79@gmail.com>
 * @copyright (c) 2011, Thomas Powers
 */
class UserProjects extends Zend_Db_Table_Abstract
{
	protected $_name	= 'up_UserProjects';

	/**
	 * Add a user to the project
	 * 
	 * @param integer $uid
	 * @param integer $pid
	 * @return boolean
	 */
	public function addUser($uid, $pid)
	{
		$u	  = new Users();
		$user = $u->find($uid)->current();
		if( $this->insert(array('userId' => $uid, 'projectId' => $pid)) ) {
            $projects   = new Projects();
            $project    = $projects->find($pid)->current();

            $message    = 'You have been added to the project ' . $project->name . '.';
			$u->sendEmail($user->email, $user->name, $message, 'Assigned to New Project');
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Fetch projects for a user
	 * 
	 * @param integer $uid
	 * @param boolean $openOnly
	 * @return Zend_Db_Table_Rowset
	 */
	public function fetchUserProjects($uid, $openOnly = false)
	{
		$select	= $this->select()->from(array('up' => $this->_name), array('userId'))
								 ->where('up.userId = ?', $uid)
								 ->join(array('p' => 'p_Projects'), 'p.id = up.projectId')
								 ->setIntegrityCheck(false);

		if($openOnly) {
			$select->where('p.status < ?', 10);
		}

		return $this->fetchAll($select);
	}

	/**
	 * Fetch the members of a project
	 * 
	 * @param integer $projectId
	 * @return Zend_Db_Table_Rowset
	 */
	public function getMembers($projectId)
	{
		$select	= $this->select()->from(array('up' => $this->_name))
								 ->where('up.projectId = ?', $projectId)
								 ->join(array('u' => 'u_Users', array('name', 'email')), 'u.id = up.userId')
								 ->order('u.name ASC')
								 ->setIntegrityCheck(false);

		return $this->fetchAll($select);
	}

	/**
	 * Checks if a user is a member
	 * 
	 * @param integer $uid
	 * @param integer $pid
	 * @return boolean
	 */
	public function isMember($uid, $pid)
	{
		$select	= $this->select()->from($this, 'id')
								 ->where('userId = ?', $uid)
								 ->where('projectId = ?', $pid);

		$result	= $this->fetchAll($select);

		return (count($result) ? true : false);
	}

	/**
	 * Remove a user from a project
	 * 
	 * @param integer $uid
	 * @param integer $pid
	 * @return boolean
	 */
	public function removeProject($uid, $pid)
	{
		$where[]	= $this->getAdapter()->quoteInto('userId = ?', $uid);
		$where[]	= $this->getAdapter()->quoteInto('projectId = ?', $pid);

		return $this->delete($where);
	}
}
