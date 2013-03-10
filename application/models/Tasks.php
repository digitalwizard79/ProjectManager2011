<?php

/**
 * Gateway object for t_Tasks
 * 
 * @author Thomas Powers <digitalwizard79@gmail.com>
 * @copyright (c) 2011, Thomas Powers
 */
class Tasks extends Zend_Db_Table_Abstract
{
	protected $_name	= 't_Tasks';
	
	/**
	 * Add a task
	 * 
	 * @param array $data
	 * @return integer
	 */
	public function add(array $data)
	{
		$data['estimateCurrent']	= $data['estimateOrig'];
		$data['elapsed']			= 0;
		$data['remaining']			= $data['estimateOrig'];
		
		return parent::insert($data);
	}
}