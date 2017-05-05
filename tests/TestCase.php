<?php

abstract class TestCase extends Illuminate\Foundation\Testing\TestCase
{
	/**
	 * The base URL to use while testing the application.
	 *
	 * @var string
	 */
	protected $baseUrl = 'http://portchris.hades.portchris.net:8081';

	/**
	 * Creates the application.
	 *
	 * @return \Illuminate\Foundation\Application
	 */
	public function createApplication()
	{
		$app = require __DIR__.'/../bootstrap/app.php';

		$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

		return $app;
	}

	public function __call($method, $args)
	{
		if (in_array($method, ['get', 'post', 'put', 'patch', 'delete'])) {
			return $this->call($method, $args[0]);
		}
		
		// throw new BadMethodCallException;
	}

	/**
	* Dumps json result
	*
	* @param string $function can be print_r, var_dump or var_export
	* @param boolean $json_decode 
	*/
	public function dump($function = 'var_export', $json_decode = true) {
		$content = $this->response->getContent();
		if ($json_decode) {
			$content = json_decode($content, true);
		}
		// ❤ ✓ ☀ ★ ☆ ☂ ♞ ☯ ☭ € ☎ ∞ ❄ ♫ ₽ ☼
		$seperator = '❤ ❤ ❤ ❤ ❤ ❤ ❤ ❤ ❤ ❤ ❤ ❤ ❤ ❤ ❤ ❤ ❤ ❤ ❤ ❤ ❤ ❤ ❤ ❤';
		echo PHP_EOL . $seperator . PHP_EOL;
		$function($content);
		echo $seperator . PHP_EOL;
		return $this;
	}
}
