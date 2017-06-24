<?php

class User
{
	public $acctid;
	protected $validTypes = ['at','de','hp','ff'];

	public function __construct($acctid = false)
	{
		if ($acctid == false)
		{
			global $session;
			$this->acctid = $session['user']['acctid'];
		}
		else
		{
			$this->acctid = $acctid;
		}
	}
	public function getData($field = 'name')
	{
		$sql = db_query("SELECT $field FROM accounts WHERE acctid = '{$this->acctid}'");
		return $res = db_fetch_assoc($sql);
	}
	public function convertDragonPoints()
	{
		$dragonPoints = unserialize($this->getData('dragonpoints'));
		debug(array_count_values($dragonPoints));
	}
	/*
	public function addDragonPoint($type = 'at')
	{
		if (!in_array($type,$this->validTypes))
		{
			debuglog('tried to add an invalid dragonpoint type. Applying attack dragonpoint instead.');
			$type = 'at';
		}
	}*/
}

?>