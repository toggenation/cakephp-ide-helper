<?php

namespace IdeHelper\Generator\Task;

use Cake\Filesystem\Folder;
use Cake\TestSuite\TestCase;
use IdeHelper\Generator\Directive\ExpectedArguments;
use IdeHelper\Utility\Plugin;
use IdeHelper\ValueObject\StringName;

class FixtureTask implements TaskInterface {

	protected const METHOD_ADD_FIXTURE = '\\' . TestCase::class . '::addFixture()';

	/**
	 * @return \IdeHelper\Generator\Directive\BaseDirective[]
	 */
	public function collect(): array {
		$result = [];

		$fixtures = $this->getFixtures();

		$method = static::METHOD_ADD_FIXTURE;
		$directive = new ExpectedArguments($method, 0, $fixtures);
		$result[$directive->key()] = $directive;

		return $result;
	}

	/**
	 * @return \IdeHelper\ValueObject\StringName[]
	 */
	protected function getFixtures(): array {
		$list = [];

		$fixtures = [];
		$fixtureFolder = ROOT . DS . 'tests' . DS . 'Fixture' . DS;
		$fixtures += $this->parseFixtures($fixtureFolder, 'app');

		$plugins = Plugin::all();
		foreach ($plugins as $plugin) {
			$path = Plugin::path($plugin);
			$fixtureFolder = $path . 'tests' . DS . 'Fixture' . DS;
			$fixtures += $this->parseFixtures($fixtureFolder, 'plugin.' . $plugin);
		}

		$fixtureFolder = ROOT . DS . 'vendor' . DS . 'cakephp' . DS . 'cakephp' . DS . 'tests' . DS . 'Fixture' . DS;
		$fixtures += $this->parseFixtures($fixtureFolder, 'core');

		foreach ($fixtures as $fixture) {
			$list[$fixture] = StringName::create($fixture);
		}

		ksort($list);

		return $list;
	}

	/**
	 * @param string $fixtureFolder
	 * @param string $domain
	 *
	 * @return string[]
	 */
	protected function parseFixtures(string $fixtureFolder, string $domain): array {
		if (!is_dir($fixtureFolder)) {
			return [];
		}

		$folder = new Folder($fixtureFolder);
		$content = $folder->read();

		$fixtures = [];
		foreach ($content[1] as $file) {
			if (substr($file, -11) !== 'Fixture.php') {
				continue;
			}
			$fixture = $domain . '.' . substr($file, 0, -11);

			$fixtures[$fixture] = $fixture;
		}

		return $fixtures;
	}

}
