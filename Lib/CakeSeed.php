<?php
/**
 * Class CakeSeed
 */
class CakeSeed extends Object {

/**
 * Connection used
 *
 * @var string
 */
	public $connection = 'default';

/**
 * Constructor
 *
 * @param array $options optional load object properties
 */
	public function __construct($options = array()) {
		parent::__construct();

		$allowed = array('connection', 'callback');

		foreach ($allowed as $variable) {
			if (!empty($options[$variable])) {
				$this->{$variable} = $options[$variable];
			}
		}

		if (is_array($this->uses) && count($this->uses) > 0) {
			foreach ($this->uses as $model) {
				App::import('Model', $model);
				$this->$model = new $model(false, null, $this->connection);
			}
		}
	}
} 