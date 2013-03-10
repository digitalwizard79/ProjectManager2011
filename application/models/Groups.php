<?php

/**
 * Gateway object for g_Groups
 * 
 * @author Thomas Powers <digitalwizard79@gmail.com>
 * @copyright (c) 2011, Thomas Powers
 */
class Groups extends Zend_Db_Table_Abstract
{
	protected $_name	= 'g_Groups';

	/**
	 * Fetch the group level
	 * 
	 * @param integer $gid
	 * @return string
	 */
	public function getLevel($gid)
	{
		$select	= $this->select()->from(array('g' => $this->_name), 'level')
							   	 ->where('g.id = ?', $gid);

		$result	= $this->fetchRow($select);

		return $result->level;
	}
}