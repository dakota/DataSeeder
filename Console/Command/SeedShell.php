<?php

// Components used
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');
App::uses('AppShell', 'Console/Command');

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
class SeedShell extends AppShell {

/**
 * Current path to load and save seed files
 *
 * @var string
 */
	public $path = null;

/**
 * Connection used for the migration_schema table of the migration versions
 *
 * @var null|string
 */
	public $connection = null;

/**
 * Source of the seed, can be 'app' or a plugin name
 *
 * @var string
 */
	public $source = 'app';

/**
 * Type of seed
 *
 * @var null|string
 */
	public $type = null;

/**
 * Seed to run
 *
 * @var null|string
 */
	public $seed = null;

	public function startup() {
		$this->out(__d('data_seed', 'Cake Database Seeder Shell'));
		$this->hr();

		if (!empty($this->params['connection'])) {
			$this->connection = $this->params['connection'];
		}

		if (!empty($this->params['plugin'])) {
			$this->source = $this->params['plugin'];
		}

		if (!empty($this->params['seed'])) {
			$this->seed = $this->params['seed'];
		}

		if (!empty($this->params['type'])) {
			$this->type = $this->params['type'];
		}

		$this->path = $this->_getPath() . 'Config' . DS . 'Seeds' . DS;
	}

	/**
	 * Get the option parser.
	 *
	 * @return
	 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();

		return $parser->description('The database seed shell.')
			->addOption('plugin', array(
				'short' => 'p',
				'help' => __('Plugin name to be used')))
			->addOption('connection', array(
				'short' => 'c',
				'default' => null,
				'help' => __('Overrides the \'default\' connection')))
			->addOption('seed', array(
				'short' => 's',
				'default' => null,
				'help' => __('Specify the seed file to run')))
			->addOption('type', array(
				'short' => 't',
				'default' => null,
				'choices' => array('up', 'down', 'both'),
				'help' => __('Specify the type of seed')))
			->addSubcommand('seed', array(
			'help' => __('Seeds the database')))
			->addSubcommand('generate', array(
			'help' => __('Generates a empty seed file.')));
	}

	public function main() {
		$this->out($this->getOptionParser()->help());
	}

/**
 * Return the path used
 *
 * @param string $type Can be 'app' or a plugin name
 *
 * @return string Path used
 */
	protected function _getPath($type = null) {
		if ($type === null) {
			$type = $this->source;
		}
		if ($type !== 'app') {
			return App::pluginPath($type);
		}

		return APP;
	}

	public function generate() {
		while (true) {
			$name = $this->in(__d('data_seed', 'Please enter the descriptive name of the seed to generate:'));
			if (!preg_match('/^([A-Za-z0-9_]+|\s)+$/', $name) || is_numeric($name[0])) {
				$this->out('');
				$this->err(__d('data_seed', 'Seed name (%s) is invalid. It must only contain alphanumeric characters and start with a letter.', $name));
			} elseif (strlen($name) > 255) {
				$this->out('');
				$this->err(__d('data_seed', 'Seed name (%s) is invalid. It cannot be longer than 255 characters.', $name));
			} else {
				$name = str_replace(' ', '_', trim($name));
				break;
			}
		}
		$this->out(__d('data_seed', 'Generating an empty seed file...'));
		$this->_writeSeed($name);
		$this->out('');
		$this->out(__d('data_seed', 'Done.'));
	}

	protected function _writeSeed($name) {
		$content = $this->_generateEmptySeed($name, Inflector::camelize($name));
		$File = new File($this->path . Inflector::camelize($name) . 'Seed.php', true);

		return $File->write($content);
	}

	protected function _generateEmptySeed($name, $class) {
		return $this->_generateTemplate('seed', compact('name', 'class'));
	}

/**
 * Include and generate a template string based on a template file
 *
 * @param string $template Template file name
 * @param array  $vars     List of variables to be used on tempalte
 *
 * @return string
 */
	protected function _generateTemplate($template, $vars) {
		extract($vars);
		ob_start();
		ob_implicit_flush(0);
		include dirname(__FILE__) . DS . 'Templates' . DS . $template . '.ctp';
		$content = ob_get_clean();

		return $content;
	}

	public function seed() {
		if (empty($this->seed)) {
			$this->seed = $this->_getSeed();
		} else {
			$this->seed .= 'Seed';
		}

		if (empty($this->type)) {
			$this->out('Please choose one of the following options');
			$prompt = '[1] Write new seed data' . PHP_EOL . '[2] Remove old seed data' . PHP_EOL . '[3] Both (option 2 + 1)' . PHP_EOL;
			$seedType = strtolower($this->in($prompt, array('1', '2', '3'), '3'));
		} else {
			$seedType = $this->type == 'both' ? '3' : ($this->type == 'up' ? '1' : '2');
		}

		// set seed filename
		$seedFile = $this->path . $this->seed . '.php';

		if (file_exists($seedFile) && is_readable($seedFile)) {
			// load file and set class
			App::uses('CakeSeed', 'DataSeeder.Lib');
			require_once $seedFile;
			$class = $this->seed;

			if (!class_exists($class)) {
				$this->error('Unable to find class ' . $class . ' in seed file ' . $seedFile);

				return $this->_stop();
			}

			// initialize class and load needed models
			$Seed = new $class(array(
				'connection' => $this->connection,
				'callback' => &$this
			));

			$log = '';
			// run down method to remove previous seed data
			if (method_exists($Seed, 'down') && ($seedType == '2' || $seedType == '3')) {
				$Seed->down();
				$log .= '+ Previous seed data removed' . PHP_EOL;
			}

			// inset new seed data
			if (method_exists($Seed, 'up') && ($seedType == '1' || $seedType == '3')) {
				$Seed->up();
				$log .= '+ New seed data written' . PHP_EOL;
			}

			$this->out($class . PHP_EOL . $log);

			// stop executing
			return $this->_stop();
		} else {
			$this->error('Unable to read seed file under:' . PHP_EOL . $seedFile);

			return $this->_stop();
		}
	}

	protected function _getSeed() {
		$availableSeeds = $this->_getAvailableSeeds();
		if (count($availableSeeds) > 0) {
			$this->out('Following seeds are available, which one would you like to load?');

			$prompt = array();
			foreach ($availableSeeds as $key => $seed) {
				$prompt[$key] = '[' . $key . '] ' . $seed;
			}

			$prompt['q'] = '[Q] Quit';

			$seedToUse = strtolower($this->in(implode(PHP_EOL, $prompt), null, 'Q'));

			if ($seedToUse == 'q') {
				return $this->_stop();
			} elseif (!is_numeric($seedToUse)) {
				$this->error('The option you choose is invalid. Please try again.');

				return $this->_stop();
			} else {
				$seedKey = (int)$seedToUse;
				if (isset($availableSeeds[$seedKey])) {
					return $availableSeeds[$seedKey];
				} else {
					$this->error('The choosen option could not be found: ' . $seedKey);

					return $this->_stop();
				}
			}
		}
	}

	protected function _getAvailableSeeds() {
		$dir = new Folder($this->path);
		$files = $dir->find('(.*)Seed\.php');
		$seeds = array();

		foreach ($files as $file) {
			$seeds[] = reset(explode('.', $file));
		}
		sort($seeds);

		return $seeds;
	}
}