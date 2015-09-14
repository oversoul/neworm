<?php
/**
 * Build all queries but doesn't execute any.
 */

class Query {


	protected $model = null;
	public $distinct = null;
	public $tableName;
	public $tableAlias = null;
	public $fields = '';
	public $wheres = '';
	public $values = array();
	public $group_by = array();
	public $having = '';
	public $order = false;
	public $orderBy = 'ASC';
	public $limit = false;
	public $offset = false;
	public $_join_sources = array();
	protected $is_new = false;


	/**
	 * if the an object was sent then set the object
	 * and get the table from it.
	 * 
	 * @param object|string $model
	 */
	public function __construct($model)
	{
		if ( is_object($model) ) {
			$this->model = $model;
			$this->fields = $this->getFields($model, true);
			$this->tableName = $model->getTableName();
			return $this;
		}
		$this->tableName = $model;
		return $this;
	}

	public function getFields($model, $alias = false)
	{
		$fields = [];

		foreach (get_object_vars($model) as $property => $value) {
			$fields[] = '`' . $model->getTableName() . '`.`' . $property .'`'. $this->alias($model, $property, $alias);
		}
		return implode(', ', $fields);
	}

	public function alias($model, $property, $allow = false)
	{
		if ( $allow ) return '';
		return  ' as `'.$model->getTableName().'-'.$property.'`';
	}

	public function appendFields($model)
	{
		$fields = $this->getFields($model);
		if ( $this->fields === '' ) {
			$this->fields = $fields;
		} else {
			$this->fields = $this->fields . ', ' . $fields;
		}
		return $this;
	}


	public function limit($limit) {
		$this->limit = $limit;
		return $this;
	}

	public function offset($offset) {
		$this->offset = $offset;
		return $this;
	}

	public function distinct()
	{
		$this->distinct = true;
		return $this;
	}

	public function groupBy($column)
	{
		$this->group_by = $column;
		return $this;
	}

	public function having($column)
	{
		$this->having = $column;
		return $this;
	}

	public function asc($field)
	{
		$this->orderBy = $field;
		$this->order = "ASC";
		return $this;
	}

	public function desc($field)
	{
		$this->orderBy = $field;
		$this->order = "DESC";
		return $this;
	}

	public function where($cond, $value = array())
	{
		$this->wheres = $cond;

		if( ! empty($value) ) $this->bindValues($value);
		
		return $this;
	}

	public function whereIn($key, $values = array())
	{
		$holders = array_fill(0, count($values), '?');
		
		$this->wheres = $key . ' IN (' . implode(', ', $holders) . ')';

		$this->bindValues($values);
		
		return $this;
	}
	
	public function whereId($id)
	{
		$this->wheres = 'id = ?';
		$this->bindValues($id);
		return $this;
	}

	public function leftJoin($table, $constraint)
	{
		$this->join('LEFT', $table, $constraint);
	}

	/**
	 * Internal method to add a JOIN source to the query.
	 */
	public function join($type, $table, $constraint)
	{

		$type  = trim("{$type} JOIN");
		$table = "`{$table}`";

		// Build the constraint
		if ( is_array($constraint) ) {
			list( $first_column, $operator, $second_column ) = $constraint;
			$constraint = "{$first_column} {$operator} {$second_column}";
		}

		$this->_join_sources[] = "{$type} {$table} ON {$constraint}";
		return $this;
	}

	public function bindValues($values)
	{
		$this->values = array_merge($this->values, (array) $values);
	}
	

	/**
	 * Query Select builder.
	 */
	public function select()
	{
		return [ ( new Builder($this) )->select(), $this->values ];
	}

	public function getModel()
	{
		return $this->model;
	}
}