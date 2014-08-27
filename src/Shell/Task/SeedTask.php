<?php
namespace DataSeeder\Shell\Task;

use Cake\Shell\Task\SimpleBakeTask;

/**
 * Report shell command.
 */
class SeedTask extends SimpleBakeTask {

/**
 * Task name used in path generation.
 *
 * @var string
 */
	public $pathFragment = 'Seed/';

/**
 * {@inheritDoc}
 */
	public function name() {
		return 'seed';
	}

/**
 * {@inheritDoc}
 */
	public function fileName($name) {
		return $name . 'Seed.php';
	}

/**
 * {@inheritDoc}
 */
	public function template() {
		return 'seed';
	}

/**
 * {@inheritDoc}
 */
	public function bakeTest($className) {
		return $className;
	}

/**
 * {@inheritDoc}
 */
	public function bake($name = null) {
		if (empty($this->Template->params['template']) || $this->Template->params['template'] === 'default') {
			$this->Template->params['template'] = 'seeder';
		}
		parent::bake($name);
	}

}
