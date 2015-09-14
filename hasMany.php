<?php
class hasMany {

	protected $parent;
	protected $related;

	protected $foreignKey;
	protected $localKey;
	protected $data = [];

	/**
	 * Create a new relation instance.
	 *
	 * @return void
	 */
	public function __construct($related, $parent, $foreignKey, $localKey)
	{
		$this->related = $related;
		$this->parent = $parent;

		$this->localKey = $localKey;
		$this->foreignKey = $foreignKey;
		
	}

	public function match(&$data)
	{
		$ids = [];
		foreach ($data as $key => $value) {
			if ( $key === $this->localKey ) {
				$ids[] = $value;
			}
		}
		$data[$this->related->getTableName()] = $this->related->whereIn($this->foreignKey, $ids)->all();
	}

	public function matchMany(&$data)
	{
		$ids = [];
		foreach ($data as $value) {
			if ( isset($value[$this->localKey]) ) {
				$ids[] = $value[$this->localKey];
			}
		}

		$result = $this->related->whereIn($this->foreignKey, $ids)->all();
		$result = $this->group($result);
	
		$table = $this->related->getTableName();
		foreach ($data as $key => &$value) {
			$data[$key][$table] = $result[ $value[$this->localKey] ];
		}
	}

	public function group($data)
	{
		$result = [];
		foreach ($data as $key => $value) {
			$result[ $value[ $this->foreignKey ] ][] = $value;
		}
		return $result;
	}


}
