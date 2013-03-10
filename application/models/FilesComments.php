<?php

/**
 * Gateway object for fc_FilesComments
 * 
 * @author Thomas Powers <digitalwizard79@gmail.com>
 * @copyright (c) 2011, Thomas Powers
 */
class FilesComments extends Zend_Db_Table_Abstract
{
	protected $_name	= 'fc_FilesComments';

	/**
	 * Fetch all comments on an existing file
	 * 
	 * @param integer $fid
	 * @return Zend_Db_Table_Rowset
	 */
	public function fetchCommentsByFile($fid)
	{
		$select	= $this->select()->from(array('fc' => $this->_name))
								 ->join(array('u' => 'u_Users'), 'u.id = fc.author', array('name', 'email'))
								 ->where('fileId = ?', $fid)
								 ->order('dateAdded ASC')
								 ->setIntegrityCheck(false);

		return $this->fetchAll($select);
	}
}