<?php

/**
 * Gateway object for u_Users
 * 
 * @author Thomas Powers <digitalwizard79@gmail.com>
 * @copyright (c) 2011, Thomas Powers
 */
class Users extends Zend_Db_Table_Abstract
{
	protected $_name	= 'u_Users';

	/**
	 * Create a user for access to application
	 * 
	 * @param string $name
	 * @param string $email
	 * @return integer
	 */
	public function autoCreate($name, $email) {
		$data	= array(
			'username'		=> $email,
			'password'		=> md5(time()),
			'name'			=> $name,
			'email'			=> $email,
			'primaryGroup'	=> 3,
			'status'		=> -1,
			'challenge'		=> substr(md5(time()), 0, 10),
		);

		$this->insert($data);
		$uid	= $this->getAdapter()->lastInsertId();

		$this->sendRegistration($data['email'], $data['name'], $data['challenge']);

		return $uid;
	}

	/**
	 * Fetch all users
	 * 
	 * @return Zend_Db_Table_Rowset
	 */
	public function fetchAllUsers()
	{
		$select	= $this->select()->from(array('u' => $this->_name))
								 ->join(array('g' => 'g_Groups'), 'u.primaryGroup = g.id', array('groupName' => 'name'))
								 ->order('u.name ASC')
								 ->setIntegrityCheck(false);

		return $this->fetchAll($select);
	}

	/**
	 * Fetch user by email address
	 * 
	 * @param string $email
	 * @return Zend_Db_Table_Rowset
	 */
	public function fetchByEmail($email)
	{
		$select	= $this->select()->where('status = -1')
								 ->where('email = ?', $email);

		return $this->fetchRow($select);
	}

	/**
	 * Fetch admin users
	 * 
	 * @return Zend_Db_Table_Rowset
	 */
	public function fetchProjectManagers()
	{
		$select	= $this->select()->where('primaryGroup = ?', 2);
		return $this->fetchAll($select);
	}

	/**
	 * Check if admin user
	 * 
	 * @param integer $gid
	 * @return boolean
	 */
	public function isAdmin($gid)
	{
		$g	= new Groups();
		return ($g->getLevel($gid) <= 1 ? true : false);
	}

	/**
	 * Check the confirmation to see if it's valid
	 * 
	 * @param string $email
	 * @param string $conf
	 * @return boolean
	 */
	public function isValidConfirmation($email, $conf)
	{
		$select	= $this->select()->where('status = -1')
								 ->where('email = ?', $email)
								 ->where('challenge = ?', $conf);

		$result	= $this->fetchAll($select);
		return (count($result) ? true : false);
	}

	/**
	 * Register the user
	 * 
	 * @param integer $id
	 * @param string $name
	 * @param string $password	 
	 */
	public function register($id, $name, $password)
	{
		$data['name']			= $name;
		$data['password']		= md5($password);
		$data['status']			= 1;
		$data['dateActivated']	= date('Y-m-d h:i:s', time());

		$this->update($data, 'id = ' . $id . ' AND status = -1');
	}

	/**
	 * Send email
	 * 
	 * @param string $email
	 * @param string $name
	 * @param string $message
	 * @param string $subject
	 */
	public function sendEmail($email, $name, $message, $subject) 
	{
		$mail	= new Zend_Mail();
		$mail->setBodyText($message);
		$mail->setFrom('noreply@noreply.com', 'Project Manager');
		$mail->addTo($email, $name);
		$mail->setSubject($subject);
		
		$mail->send();
	}

	/**
	 * Send registration email
	 * 
	 * @param string $email
	 * @param string $name
	 * @param string $challenge
	 */
	public function sendRegistration($email, $name, $challenge)
	{
		$message	= "<< This message is automatically generated >>\n\nYou have been added as a Team Member in the Project Manager. Please go to the project manager website and click on 'Register New User' and use the following invite code:\n\n$challenge";
		$subject	= 'Project Manager Registration';

		$this->sendEmail($email, $name, $message, $subject);
	}
}