<?php
namespace DataSeeder\Seed;

use Cake\Console\Shell;
use Cake\ORM\Table;

/**
 * Class BaseSeed
 */
class BaseSeed extends Shell {

/**
 * Initialize hook.
 *
 * Populates the connection property, which is useful for tasks of tasks.
 *
 * @return void
 */
	public function initialize() {
		if (empty($this->connection) && !empty($this->params['connection'])) {
			$this->connection = $this->params['connection'];
		}

		foreach ($this->uses as $use) {
			$this->loadModel($use);
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

/**
 * The main method that actually does the seed
 *
 * @return void
 */
	public function main() {
		if (empty($this->params['direction'])) {
			$this->out('Please choose one of the following options');
			$prompt = '[1] Write new seed data' . PHP_EOL . '[2] Remove old seed data' . PHP_EOL . '[3] Both (option 2 + 1)' . PHP_EOL;
			$choice = strtolower($this->in($prompt, ['1', '2', '3'], '3'));
			$direction = ['up', 'down', 'both'][$choice - 1];
		} else {
			$direction = $this->params['direction'];
		}

		// run down method to remove previous seed data
		if (method_exists($this, '_down') && ($direction == 'down' || $direction == 'both')) {
			$this->_down();
			$this->out('+ Previous seed data removed');
		}

		// inset new seed data
		if (method_exists($this, '_up') && ($direction == 'up' || $direction == 'both')) {
			$this->_up();
			$this->out('+ New seed data written');
		}
	}

/**
 * Get the option parser for this task.
 *
 * This base class method sets up some commonly used options.
 *
 * @return \Cake\Console\ConsoleOptionParser
 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();
		$parser
			->addOption('connection', [
				'short' => 'c',
				'default' => 'default',
				'help' => 'The datasource connection to get data from.'
			])
			->addOption('direction', [
				'short' => 'd',
				'default' => '',
				'help' => 'The direction to seed',
				'choices' => [
					'up',
					'down',
					'both'
				]
			]);

		if (!empty($this->description)) {
			$parser
				->description($this->description);
		}

		return $parser;
	}

/**
 * Displays a simple progress report
 *
 * @param int $numberDone Number that has been completed
 * @param int $totalNumber Total to complete
 * @param bool $fail Did the step fail?
 * @return void
 */
	protected function _displayProgress($numberDone, $totalNumber = null, $fail = false) {
		$this->out(!$fail ? '.' : '<error>F</error>', 0, Shell::QUIET);
		if ($numberDone % 70 === 0 && $totalNumber) {
			$percent = ceil(($numberDone / $totalNumber) * 100);
			$this->out(" $numberDone / $totalNumber ({$percent}%)", 1, Shell::QUIET);
		} elseif ($numberDone % 70 === 0) {
			$this->out(" $numberDone", 1, Shell::QUIET);
		}
	}
}
