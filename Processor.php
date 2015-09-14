<?php 
class Processor {

	
	public function first( $query )
	{
		list($query, $values) = $query->select();
		
		$statement = Database::execute($query, $values);
		
		return $statement->fetch();
	}

	public function all( $query )
	{

		list($query, $values) = $query->select();
		
		$statement = Database::execute($query, $values);
		
		return $statement->fetchAll();
	}

}