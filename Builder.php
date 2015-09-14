<?php 
class Builder  {

	protected $query;


	function __construct($query) {
		$this->query = $query;
	}	

	public function select()
	{
		$queryArray = array_filter([
			$this->getSelect(),
			$this->getJoin(),
			$this->getWhere(),
			$this->getGroupBy(),
			$this->getHaving(),
			$this->getOrderBy(),
			$this->getLimit(),
			$this->getOffset()
		], function ($key) { return ($key != "") ? $key : ""; });
		return implode(' ', $queryArray);
	}

	public function update( $data )
	{
		$query = array();
		$query[] = "UPDATE `{$this->query->tableName}` SET";

		$field_list = array();
		$values = array();
		foreach ( $data as $key => $value ) {
			$field_list[] = "`{$key}` = :{$key}";
			$values[":{$key}"] = $value;
		}

		$query[] = join(", ", $field_list);

		$values[':id'] = $this->query->get('id');
		$this->query->bindValues($values);
		
		return join(" ", $query)." WHERE `{$this->query->tableName}`.`id` = :id";
	}

	public function insert( $dirty_fields )
	{
		$query[] = "INSERT INTO";
		$query[] = "`{$this->query->tableName}`";
		$field_list = array_keys( $dirty_fields );
		$query[] = "(" . join(", ", $field_list) . ")";
		$query[] = "VALUES";

		$placeholders = $this->_create_placeholders( $dirty_fields );
		$query[] = "({$placeholders})";
		return join(" ", $query);
	}

	public function delete()
	{
		return join(" ", array(
			"DELETE FROM",
			"`{$this->query->tableName}`",
			"WHERE id = ?"
		));
	}

	public function deleteMany()
	{
		$queryArray = array_filter(
			[
				"DELETE FROM",	"`{$this->query->tableName}`",	$this->getWhere()
			],
			function ($key) {
				return ($key != "") ? $key : ""; 
			}
		);
		return implode(' ', $queryArray);
	}

	
	/**
	 * Return a string containing the given number of question marks,
	 * separated by commas. Eg "?, ?, ?"
	 */
	protected function _create_placeholders($fields) {
		if ( ! empty( $fields ) ) {
			$db_fields = array();
			foreach($fields as $key => $value) {
				$db_fields[] = '?';
			}
			return implode(', ', $db_fields);
		}
	}

	protected function getSelect()
	{
		$query = "SELECT ";
		if( $this->query->distinct ) $query .= "DISTINCT ";

		$query .= is_array($this->query->fields) ? implode(', ', $this->query->fields) : $this->query->fields;

		$query .= " FROM `{$this->query->tableName}`";
		return $query;
	}

	protected function getJoin()
	{
		if (count($this->query->_join_sources) === 0) return '';

		return join(" ", $this->query->_join_sources);
	}

	protected function getWhere()
	{
		if ($this->query->wheres != "") return "WHERE ". $this->query->wheres;

		return "";
	}

	protected function getGroupBy()
	{
		if (count($this->query->group_by) === 0) return '';
		
		return "GROUP BY " . $this->query->group_by;
	}

	protected function getHaving()
	{
		if( $this->query->having != "") return "HAVING ".$this->query->having;
	}

	protected function getOrderBy()
	{
		if($this->query->order) return "ORDER BY ".$this->query->orderBy." ".$this->query->order;
		
		return "";
	}

	protected function getLimit()
	{
		if ($this->query->limit) return "LIMIT ".$this->query->limit;
	}

	protected function getOffset()
	{
		if ($this->query->offset) return "OFFSET ".$this->query->offset;
	}

}