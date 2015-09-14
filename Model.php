<?php
class Model {

	protected $query;
	protected $processor;
	protected $hasManyModels = [];
	protected $models = [];


	function __construct($data = array()) {

		if ( ! empty($data) ) {
			foreach ($data as $key => $value) {
				$this->$key = $value;
			}
		}

		$this->query = new Query( $this );
		$this->processor = new Processor;
	}


	public function limit($limit) {
		$this->query->limit($limit);
		return $this;
	}

	public function offset($offset) {
		$this->query->offset($offset);
		return $this;
	}

	public function distinct()
	{
		$this->query->distinct();
		return $this;
	}

	public function whereId($id)
	{
		$this->query->whereId($id);
		return $this;
	}

	public function where($cond, $value = array())
	{
		$this->query->where($cond, $value);
		return $this;
	}

	public function whereIn($foreignKey, $values = [])
	{
		$this->query->whereIn($foreignKey, $values);
		return $this;
	}


	public function groupBy($column)
	{
		$this->query->groupBy($column);
		return $this;
	}

	public function having($column)
	{
		$this->query->having($column);
		return $this;
	}

	public function asc($field)
	{
		$this->query->asc($field);
		return $this;
	}

	public function desc($field)
	{
		$this->query->desc($field);
		return $this;
	}


	/**
	 * Belongs To relationship.
	 * @param  string  $class      
	 * @param  string  $foreignKey
	 * @param  string $table this table
	 * @return Model
	 */
	public function belongsTo($related, $foreignKey, $table = false)
	{
		$instance = new $related;

		$callers = debug_backtrace();
		$name = $callers[1]['function'];

		$localKey = '`' . $instance->getTableName() . '`.`id`';

		if ( ! $table ) $table = $this->getTableName();
		$foreignKey = '`' . $table . '`.`' . $foreignKey . '`';

		$this->models[$name] = new belongsTo($instance, $this->query, $foreignKey, $localKey);

		return $this;
	}

	/**
	 * Has One relationship
	 * @param  string  $related
	 * @param  string  $foreignKey
	 * @param  string $table
	 * @return Model
	 */
	public function hasOne($related, $foreignKey, $table = false)
	{
		$instance = new $related;

		$callers = debug_backtrace();
		$name = $callers[1]['function'];

		if ( ! $table ) $table = $this->getTableName();
		$localKey   = '`' . $table . '`.`id`';

		$foreignKey = '`' . $instance->getTableName().'`.`' . $foreignKey . '`';

		$this->models[$name] = new HasOne($instance, $this->query, $foreignKey, $localKey);

		return $this;
	}

	public function hasMany($related, $foreignKey, $table = false)
	{
		$instance = new $related;

		$callers = debug_backtrace();
		$name = $callers[1]['function'];

		$localKey   = 'id';

		$this->models[$name] = new HasMany($instance, $this->query, $foreignKey, $localKey);

		return $this;
	}


	public function all()
	{
		$data = $this->processor->all( $this->query );

		if ( empty($data) ) return null;
		if (count($this->models) > 0)
		{
			$this->loadManyRelations($data, $this->models);
		}
		

		return $data;
	}

	public function first($id = null)
	{
		if ( ! is_null($id) ) 
			$this->where('`'.$this->getTableName().'`.`id` = ?', $id);

		$this->limit(1);
		
		$data = $this->processor->first( $this->query );
		

		if ( empty($data) ) return null;
		if (count($this->models) > 0)
		{
			$this->loadRelations($data, $this->models);
		}
		
		return $data;
	}

	public function loadRelations(&$data, $models)
	{
		foreach ($models as $class => $relation) {
			$relation->match($data);
		}
	}

	public function loadManyRelations(&$data, $models)
	{
		foreach ($models as $class => $relation) {
			$relation->matchMany($data);
		}
	}

	public function getTableName()
	{
		return strtolower(end(explode('\\', static::class)));
	}

}