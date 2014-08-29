<?php
namespace DataSeeder\Shell;

// Components used
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\Datasource\ConnectionManager;
use Cake\Core\ConventionsTrait;
use Cake\Utility\Inflector;

/**
 * Seed Shell
 *
 * This is an seed shell for CakePHP. It allows you to run multiple seeds for your application to
 * make using test data more easier.
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author        Julius Ehrlich (julius@ehrlich-bros.de)
 * @copyright     Copyright 2012, Ehrlich Bros. (http://ehrlich-bros.de)
 * @link          https://github.com/jlis/Cake-Seed-Shell
 * @package       app.Plugin.Shell
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class SeedShell extends Shell {

	use ConventionsTrait;

/**
 * Connection used for the migration_schema table of the migration versions
 *
 * @var null|string
 */
	public $connection = 'default';

/**
 * Start the shell
 *
 * @return void
 */
	public function startup() {
		$this->out(__d('data_seed', 'Cake Database Seeder Shell'));
		$this->hr();

		$task = $this->_camelize($this->command);
		if (isset($this->{$task}) && !in_array($task, ['Project'])) {
			if (isset($this->params['connection'])) {
				$this->{$task}->connection = $this->params['connection'];
			}
		}
		if (isset($this->params['connection'])) {
			$this->connection = $this->params['connection'];
		}
	}

/**
 * Override main() to handle action
 *
 * @return void
 */
	public function main() {
		$connections = ConnectionManager::configured();
		if (empty($connections)) {
			$this->out('Your database configuration was not found.');
			$this->out('Add your database connection information to config/app.php.');

			return false;
		}
		$this->out('The following commands can be used to run configured data seeders.');
		$this->out('');
		$this->out('<info>Available seeds:</info>');
		$this->out('');
		foreach ($this->tasks as $task) {
			list(, $name) = pluginSplit($task);
			$this->out('- ' . Inflector::underscore($name));
		}
		$this->out('');
		$this->out('By using <info>Console/cake DataSeeder.seed [name]</info> you can invoke a specific seed task.');
	}

/**
 * Locate the tasks bake will use.
 *
 * Scans the following paths for tasks that are subclasses of
 * Cake\Console\Command\Task\BakeTask:
 *
 * - Cake/Console/Command/Task/
 * - App/Console/Command/Task/
 * - Console/Command/Task for each loaded plugin
 *
 * @return void
 */
	public function loadTasks() {
		$tasks = [];
		$tasks = $this->_findSeeds($tasks, APP, Configure::read('App.namespace'));
		foreach (Plugin::loaded() as $plugin) {
			$tasks = $this->_findSeeds(
				$tasks,
				Plugin::classPath($plugin),
				$plugin,
				$plugin
			);
		}
		$this->tasks = array_keys($tasks);
		$this->_taskMap = $tasks;
		$this->taskNames = array_merge($this->taskNames, array_keys($this->_taskMap));
	}

/**
 * Append matching seeds in $path to the $tasks array.
 *
 * @param array  $tasks     The task list to modify and return.
 * @param string $path      The base path to look in.
 * @param string $namespace The base namespace.
 *
 * @return array Updated tasks.
 */
	protected function _findSeeds($tasks, $path, $namespace) {
		$path .= 'Seed';
		if (!is_dir($path)) {
			return $tasks;
		}
		$candidates = $this->_findClassFiles($path, $namespace);
		$classes = $this->_findSeedClasses($candidates);
		foreach ($classes as $class) {
			list(, $name) = namespaceSplit($class);
			$name = substr($name, 0, -4);
			$tasks[$name] = [
				'class' => $class,
				'config' => []
			];
		}

		return $tasks;
	}

/**
 * Find task classes in a given path.
 *
 * @param string $path      The path to scan.
 * @param string $namespace Namespace.
 *
 * @return array An array of files that may contain bake tasks.
 */
	protected function _findClassFiles($path, $namespace) {
		$iterator = new \DirectoryIterator($path);
		$candidates = [];
		foreach ($iterator as $item) {
			if ($item->isDot() || $item->isDir()) {
				continue;
			}
			$name = $item->getBasename('.php');
			$candidates[] = $namespace . '\Seed\\' . $name;
		}

		return $candidates;
	}

/**
 * Find bake tasks in a given set of files.
 *
 * @param array $files The array of files.
 *
 * @return array An array of matching classes.
 */
	protected function _findSeedClasses($files) {
		$classes = [];
		foreach ($files as $className) {
			if (!class_exists($className)) {
				continue;
			}
			$reflect = new \ReflectionClass($className);
			if (!$reflect->isInstantiable()) {
				continue;
			}
			if (!$reflect->isSubclassOf('DataSeeder\Seed\BaseSeed')) {
				continue;
			}
			$classes[] = $className;
		}

		return $classes;
	}

/**
 * Get the option parser.
 *
 * @return \Cake\Console\ConsoleOptionParser
 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();

		$parser
			->description('The database seed shell.')
			->addOption('connection', [
				'short' => 'c',
				'default' => null,
				'help' => __('Overrides the \'default\' connection')]);

		foreach ($this->_taskMap as $task => $config) {
			$taskParser = $this->{$task}->getOptionParser();
			$parser->addSubcommand(Inflector::underscore($task), [
				'help' => $taskParser->description(),
				'parser' => $taskParser
			]);
		}

		return $parser;
	}
}