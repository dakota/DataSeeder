<?php
namespace DataSeeder\Lib;
use Cake\Model\ModelAwareTrait;
use Cake\ORM\Table;

/**
 * Class CakeSeed
 */
class CakeSeed {

	use ModelAwareTrait;

/**
 * Connection used
 *
 * @var string
 */
	public $connection = 'default';

	public $uses = array();

/**
 * @var \Cake\Console\Shell
 */
	public $callback;

/**
 * Constructor
 *
 * @param array $options optional load object properties
 */
	public function __construct($options = array()) {
		$this->modelFactory('Table', ['Cake\ORM\TableRegistry', 'get']);

		$allowed = array('connection', 'callback');

		foreach ($allowed as $variable) {
			if (!empty($options[$variable])) {
				$this->{$variable} = $options[$variable];
			}
		}

		if (is_array($this->uses) && count($this->uses) > 0) {
			foreach ($this->uses as $model) {
				$this->loadModel($model);
			}
		}
	}

/**
 * Truncate a table
 *
 * @param \Cake\ORM\Table $table
 *
 * @return void
 */
	public function truncateTable(Table $table) {
		$connection = $table->connection();
		$schemaTable = new \Cake\Database\Schema\Table($table->table());
		$sql = $schemaTable->truncateSql($connection);
		foreach ($sql as $stmt) {
			$connection->execute($stmt)->closeCursor();
		}
	}
} 