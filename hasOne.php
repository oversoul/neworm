<?php
class hasOne {

	protected $parent;
	protected $related;

	protected $foreignKey;
	protected $localKey;

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


		$this->addConstraints();
	}

	public function addConstraints()
	{
		$this->parent->appendFields( $this->related )->leftJoin(
			$this->related->getTableName(), [ $this->localKey, '=', $this->foreignKey]
		);
	}

	public function match(&$data)
	{
		$keys = get_object_vars($this->related);
		foreach ($keys as $key => $value) {
			$field = $this->related->getTableName() . '-' . $key;
			$data[$this->related->getTableName()][$key] = $data[ $field ];
			unset($data[ $field ]);
		}
	}

	public function matchMany(&$data)
	{
		foreach ($data as &$value) {
			$this->match($value);
		}
	}

}
