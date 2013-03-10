<?php

/**
 * Gateway object for td_TargetDates
 * 
 * @author Thomas Powers <digitalwizard79@gmail.com>
 * @copyright (c) 2011, Thomas Powers
 */
class TargetDates extends Zend_Db_Table_Abstract
{
	protected $_name	= 'td_TargetDates';

	/**
	 * Add a target date
	 * 
	 * @param array $data
	 * @return integer
	 */
	public function add(array $data)
	{
		return parent::insert($data);
	}
}