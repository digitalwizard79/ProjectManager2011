<?php

/**
 * Gateway object for p_Projects
 * 
 * @author Thomas Powers <digitalwizard79@gmail.com>
 * @copyright (c) 2011, Thomas Powers
 */
class Projects extends Zend_Db_Table_Abstract
{
	protected $_name = 'p_Projects';

	/**
	 * Add project data to table
	 * 
	 * @param array $data
	 */
	public function add(array $data)
	{
		$identity		= Zend_Auth::getInstance()->getIdentity();
		$data['author']	= $identity->id;
		$data['slug']	= $this->_generateSlug($data['name']);

		if( $this->insert($data) ) {
			$projectId	= $this->getAdapter()->lastInsertId();
			$up			= new UserProjects();
			$up->insert(array('userId' => $identity->id, 'projectId' => $projectId));

			$psig	= new ProjectSignatures();
			foreach($this->fetchSections() as $section) {
				$psig->add($projectId, $data['author'], $section->id);
			}
		}
	}

	/**
	 * Add a signature
	 * 
	 * @param integer $pid
	 * @param string $signature
	 * @param integer $section
	 * @return boolean
	 */
	public function addSignature($pid, $signature, $section)
	{
		$signatures	= new Signatures();
		return $signatures->add($pid, $signature, $section);
	}

	/**
	 * Change status of a project to complete
	 * 
	 * @param integer $pid
	 * @return boolean
	 */
	public function complete($pid)
	{
		$sections	= new ProjectSections();
		$project	= $this->find($pid)->current();

		$project->status = $sections->getId('Completed');
		return $project->save();
	}
	
	/**
	 * Send an email to all members associated with the project
	 * 
	 * @param integer $pid
	 * @param string $message
	 * @param string $title
	 */
	public function emailMembers($pid, $message, $title)
	{
		$u			= new Users();
		$up			= new UserProjects();
		$members	= $up->getMembers($pid);
		
		foreach($members as $member) {
			$u->sendEmail($member->email, $member->name, $message, $title);
		}
	}

	/**
	 * Approve the project for development
	 * 
	 * @param integer $pid
	 * @param string $notes
	 */
	public function approve($pid, $notes)
	{
		$project				= $this->find($pid)->current();
		$project->approved		= 1;
		$project->approvedBy	= Zend_Auth::getInstance()->getIdentity()->id;
		$project->dateApproved	= date('Y-m-d h:i:s');
		$project->status		= 2;
		$project->save();

		$users	= new Users();
		$user	= $users->find($project->author)->current();

		$mail	= new Zend_Mail();
		$mail->setBodyText("<< This message is automatically generated >>\n\nYour project " . $project->name . " has been approved.\n\n\nAdditional Notes from Project Manager:\n" . $notes);
		$mail->setFrom('noreply@heartland-ins.com', 'GMIC Project Manager');
		$mail->addto($user->email, $user->name);
		$mail->setSubject('GMIC Project Manager - Project Approved');
		$mail->send();
	}

	/**
	 * Decline a project for development
	 * 
	 * @param integer $pid
	 * @param string $notes
	 */
	public function decline($pid, $notes)
	{
		$project				= $this->find($pid)->current();
		$project->approved		= -1;
		$project->approvedBy	= Zend_Auth::getInstance()->getIdentity()->id;
		$project->dateDeclined	= date('Y-m-d h:i:s');
		$project->save();

		$users	= new Users();
		$user	= $users->find($project->author)->current();

		$mail	= new Zend_Mail();
		$mail->setBodyText("<< This message is automatically generated >>\n\nYour project " . $project->name . " has been declined.\n\n\nAdditional Notes from Project Manager:\n" . $notes);
		$mail->setFrom('noreply@heartland-ins.com', 'GMIC Project Manager');
		$mail->addto($user->email, $user->name);
		$mail->setSubject('GMIC Project Manager - Project Declined');
		$mail->send();
	}

	/**
	 * Fetch all projects
	 * 
	 * @return Zend_Db_Table_Rowset
	 */
	public function fetchAllProjects()
	{
		$select	= $this->select()->from(array('p' => $this->_name))
								 ->join(array('u' => 'u_Users'), 'p.author = u.id', array('authorName' => 'name', 'authorEmail' => 'email'))
								 ->join(array('ps' => 'ps_ProjectSections'), 'p.status = ps.id', array('statusName' => 'name'))
								 ->order('p.name ASC')
								 ->setIntegrityCheck(false);

		return $this->fetchAll($select);
	}

	/**
	 * Fetch all projects assigned to a specific user
	 * 
	 * @param integer $uid
	 * @return Zend_Db_Table_Rowset
	 */
	public function fetchAllByUser($uid)
	{
		$up	= new UserProjects();
		return $up->fetchUserProjects($uid);
	}

	/**
	 * Fetch an existing project by id
	 * 
	 * @param integer $pid
	 * @return Zend_Db_Table_Row
	 */
	public function fetchById($pid)
	{
		$select	= $this->select()->from(array('p' => $this->_name))
								 ->join(array('u' => 'u_Users'), 'p.author = u.id', array('authorName' => 'name', 'authorEmail' => 'email'))
								 ->join(array('ps' => 'ps_ProjectSections'), 'p.status = ps.id', array('statusName' => 'name'))
								 ->where('p.id = ?', $pid)
								 ->setIntegrityCheck(false);

		return $this->fetchRow($select);
	}

	/**
	 * Fetch all files for a project
	 * 
	 * @param integer $pid
	 * @return Zend_Db_Table_Rowset
	 */
	public function fetchFiles($pid)
	{
		$f	= new Files();
		return $f->fetchFilesByProject($pid);
	}

	/**
	 * Fetch projects that were opened by a specific user
	 * 
	 * @param integer $uid
	 * @return Zend_Db_Table_Rowset
	 */
	public function fetchOpenByUser($uid)
	{
		$up	= new UserProjects();
		return $up->fetchUserProjects($uid, true);
	}

	/**
	 * Fetch the owner of a project
	 * 
	 * @param integer $pid
	 * @return string
	 */
	public function fetchOwner($pid)
	{
		$select	= $this->select()->from($this, 'author')
								 ->where('id = ?', $pid);

		$row	= $this->fetchRow($select);
		return $row->author;
	}

	/**
	 * Fetch the slug
	 * 
	 * @param integer $pid
	 * @return string
	 */
	public function fetchSlug($pid) {
		$select	= $this->select()->from($this, 'slug')
								 ->where('id = ?', $pid);

		$row	= $this->fetchRow($select);
		return $row->slug;
	}

	/**
	 * Fetch sections
	 * 
	 * @return Zend_Db_Table_Rowset
	 */
	public function fetchSections()
	{
		$ps	= new ProjectSections();
		return $ps->fetchAll();
	}

	/**
	 * Fetch all team members assigned to a project
	 * 
	 * @param integer $pid
	 * @return Zend_Db_Table_Rowset
	 */
	public function fetchTeamMembers($pid)
	{
		$up	= new UserProjects();
		return $up->getMembers($pid);
	}

	/**
	 * Fetch list of unapproved (declined) projects
	 * 
	 * @return Zend_Db_Table_Rowset
	 */
	public function fetchUnapprovedProjects()
	{
		$select	= $this->select()->from(array('p' => $this->_name))
								 ->join(array('u' => 'u_Users'), 'p.author = u.id', array('authorName' => 'name', 'authorEmail' => 'email'))
								 ->join(array('ps' => 'ps_ProjectSections'), 'p.status = ps.id', array('statusName' => 'name'))
								 ->where('p.approved = -2')
								 ->order('p.name ASC')
								 ->setIntegrityCheck(false);

		return $this->fetchAll($select);
	}

	/**
	 * Generate a slug
	 * 
	 * @param string $name
	 * @return string
	 */
	protected function _generateSlug($name)
	{
		$badData	= array(' ', ',', '.', '!', '?', '@', '#', '$', '%', '^', '&', '*', '(', ')', '+', '=', '{', '}', '[', ']', ':', ';', '\\', '|');
		$goodData	= array('-', '',  '',  '',  '',  '',  '',  '',  '',  '',  '',  '',  '',  '',  '',  '',  '',  '',  '',  '',  '',  '',  '',   '');

		return strtolower(str_replace($badData, $goodData, $name));
	}

	/**
	 * Fetch list of required signatures for a project
	 * 
	 * @param integer $pid
	 * @return Zend_Db_Table_Rowset
	 */
	public function getRequiredSignatures($pid)
	{
		$psig	= new ProjectSignatures();
		return $psig->fetchRequiredSignatures($pid);
	}

	/**
	 * Fetch the status of a project
	 * 
	 * @param integer $pid
	 * @return string
	 */
	public function getStatus($pid)
	{
		$select	= $this->select()->from($this, 'status')
								 ->where('id = ?', $pid);

		$row	= $this->fetchRow($select);
		return $row->status;
	}

	/**
	 * Check if a user is a member of the project
	 * 
	 * @param integer $uid
	 * @param integer $pid
	 * @return Zend_Db_Table_Rowset
	 */
	public function isMember($uid, $pid)
	{
		$up	= new UserProjects();
		return $up->isMember($uid, $pid);
	}

	/**
	 * Update the status of the project to the next state
	 * 
	 * @param integer $pid
	 */
	public function moveForward($pid)
	{
		$project	= $this->find($pid)->current();
		$project->status++;
		$project->save();
	}

	/**
	 * Refer a project for changes before approval
	 * 
	 * @param integer $pid
	 * @param string $notes
	 */
	public function refer($pid, $notes)
	{
		$project			= $this->find($pid)->current();
		$project->approved	= 0;
		$project->save();

		$users	= new Users();
		$user	= $users->find($project->author)->current();

		$mail	= new Zend_Mail();
		$mail->setBodyText("<< This message is automatically generated >>\n\nYour pending project has been refered back to you with the following notes:\n\n" . $notes);
		$mail->setFrom('noreply@noreply.com', 'Project Manager');
		$mail->addto($user->email, $user->name);
		$mail->setSubject('Project Manager');
		$mail->send();
	}

	/**
	 * Submit a project for approval
	 * 
	 * @param integer $pid
	 */
	public function submit($pid)
	{
		$project	= $this->find($pid)->current();
		$users		= new Users();
		$project->approved	= -2;
		$project->save();

		$mail	= new Zend_Mail();
		$mail->setBodyText("<< This message is automatically generated >>\n\nA new project has been submitted for approval. Please return to the project manager website to view projects pending approval.");
		$mail->setFrom('noreply@noreply.com', 'Project Manager');
		foreach($users->fetchProjectManagers() as $user) {
			$mail->addto($user->email, $user->name);
		}
		$mail->setSubject('Project Manager - New Project Submitted');

		$mail->send();
	}
}
