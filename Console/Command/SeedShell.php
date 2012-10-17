<?php

// Components used
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

// Some helping constants
define('LS', '---------------------------------------------------------------');
define('NL', "\n");

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

  private $seeds_path, $tmp_path, $seed;

  function main() {
    $this->initialize();
  }

  function initialize() {
    // set console style
    $this->stdout->styles('success', array('text' => 'green', 'blink' => false));

    // show welcome message
    $this->welcome();

    // set seeds path
    if (empty($this->seeds_path)) {
      $this->seeds_path = APP . 'Config' . DS . 'Seeds' . DS;
    }
    //$this->out('Seeds path set to:' . NL . $this->seeds_path . NL . LS);

    // list available seeds
    $seeds = $this->get_seeds();
    if (count($seeds) > 0) {
      $this->out('Following seeds are available, which one would you like to load?');

      // make prompt
      $prompt = '';
      foreach ($seeds as $k => $s) {
        $prompt.= '[' . $k . '] ' . $s . NL;
      }

      // set additional prompt options
      $additonal_promt_options = array(
        'P' => '[P] Set custom path for seed files',
        'Q' => '[Q] Quit'
      );
      foreach ($additonal_promt_options as $option) {
        $prompt.= $option . NL;
      }

      // setup prompt
      $prompt_keys = array_merge(array_keys($seeds), array_keys($additonal_promt_options));
      $result = strtolower($this->in($prompt, $prompt_keys, 'Q'));
      
      // check prompt result
      if (strtolower($result) == 'q') {
        // quit
        return $this->_stop();
      } elseif (strtolower($result) == 'p') {
        // set new seed path
        $prompt = 'Please provide path to seed files';
        $result = strtolower($this->in($prompt));

        // set new seed path
        if (empty($result) || !is_dir($result) || !is_readable($result)) {
          $this->error('The path provided is invalid or could not be read.');
          return $this->_stop();
        } else {
          $this->seeds_path = $result;
          $this->info('Seeds path set to:' . NL . $this->seeds_path . NL . LS);
        }
      } elseif(!is_numeric($result)) {
        $this->error('The option you choosed is inavlid. Please try again.');
        return $this->_stop();
      }
      else {
        // set target seed
        $seed_key = (int)$result;
        if (isset($seeds[$seed_key])) {
          $this->seed = $seeds[$seed_key];
          $this->run();
        } else {
          $this->error('The choosen option could not be found: ' . $seed_key);
          return $this->_stop();
        }
      }
    } else {
      $this->info('No seeds available.');
    }
  }

  function get_seeds() {
    $dir = new Folder($this->seeds_path);
    $files = $dir->find('(.*)Seed\.php');
    $seeds = array();

    foreach ($files as $file) {
      $seeds[] = reset(explode('.', $file));
    }
    sort($seeds);

    return $seeds;
  }

  function run() {
    $this->out('Please choose one of the following options');
    $prompt = '[1] Write new seed data' . NL . '[2] Remove old seed data' . NL . '[3] Both (option 2 + 1)' . NL;
    $seed_option = strtolower($this->in($prompt, array('1', '2', '3'), '3'));

    // set seed filename
    $seed_file = $this->seeds_path . $this->seed . '.php';

    // disable cache
    $cache_disable = Configure::read('Cache.disable');
    Configure::write('Cache.disable', true);

    if (file_exists($seed_file) && is_readable($seed_file))
    {
      // load file and set class
      require_once $seed_file;
      $class = $this->seed;

      if (!class_exists($class)) {
        $this->error('Unable to find class ' . $class . ' in seed file ' . $seed_file);
        return $this->_stop();
      }

      // initialize class and load needed models
      $Seed = new $class;
      if (is_array($Seed->uses) && count($Seed->uses) > 0) {
        foreach ($Seed->uses as $model) {
          App::import('Model', $model);
          $Seed->$model = new $model();
        }
      }

      $log = '';
      // run down method to remove previous seed data
      if (method_exists($Seed, 'down') && ($seed_option == '2' || $seed_option == '3')) {
        $Seed->down();
        $log.= '+ Previous seed data removed' . NL;
      }

      // inset new seed data
      if (method_exists($Seed, 'up') && ($seed_option == '1' || $seed_option == '3')) {
        $Seed->up();
        $log.= '+ New seed data written' . NL;
      }

      $this->success($class . NL . $log);

      // stop executing
      return $this->_stop();
    }
    else {
      $this->error('Unable to read seed file under:' . NL . $seed_file);
      return $this->_stop();
    }

    // reenable cache
    Configure::write('Cache.disable', $cache_disable);
  }

  function error($message) {
    $this->out('<error>ERROR (' . date('Y-m-d H:i:s') . '):</error>' . NL . '<warning>' . $message . '</warning>' . NL . LS);
  }

  function info($message) {
    $this->out('<info>INFO (' . date('Y-m-d H:i:s') . '):</info>' . NL . '<warning>' . $message . '</warning>' . NL . LS);
  }

  function success($message) {
    $this->out(LS . NL . '<success>SUCCESS (' . date('Y-m-d H:i:s') . '):</success>' . NL . '<warning>' . $message . '</warning>' . LS);
  }

  function welcome() {
    $line = ' _____           _ _____ _       _ _ #|   __|___ ___ _| |   __| |_ ___| | |#|__   | -_| -_| . |__   |   | -_| | |#|_____|___|___|___|_____|_|_|___|_|_|#Version 0.1, by Ehrlich Bros. (www.ehrlich-bros.de)';
    $lines = explode('#', $line);
    $this->out(implode(NL, $lines) . NL . LS . NL);
  }

}

?>