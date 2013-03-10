<?php

/**
 * Gateway object for sig_Signatures
 * 
 * @author Thomas Powers <digitalwizard79@gmail.com>
 * @copyright (c) 2011, Thomas Powers
 */
class Signatures extends Zend_Db_Table_Abstract
{
	protected $_name	= 'sig_Signatures';

	/**
	 * Add a signature to a project
	 * 
	 * @param integer $pid
	 * @param integer $section
	 * @return integer
	 */
	public function add($pid, $section)
	{
		$sections			= new ProjectSections();
		$projectSigs		= new ProjectSignatures();
		$user				= Zend_Auth::getInstance()->getIdentity();

		$data['projectId']	= $pid;
		$data['signature']	= $user->name;
		$data['section']	= ( is_numeric($section) ? $section : $sections->getId($section) );

		$id = $this->insert($data);

		$projectSigs->capture($data['projectId'], $user->id, $data['section']);

		return $id;
	}
}