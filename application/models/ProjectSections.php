<?php

/**
 * Gateway object for ps_ProjectSections
 * 
 * @author Thomas Powers <digitalwizard79@gmail.com>
 * @copyright (c) 2011, Thomas Powers
 */
class ProjectSections extends Zend_Db_Table_Abstract
{
	protected $_name	= 'ps_ProjectSections';

	/**
	 * Retrieve section id
	 * 
	 * @param integer $section
	 * @return string
	 */
	public function getId($section)
	{
		$select	= $this->select()->from(array('ps' => $this->_name), 'id')
								 ->where('ps.name = ?', $section);

		$result	= $this->fetchRow($select);

		return $result->id;
	}
}