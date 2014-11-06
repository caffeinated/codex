<?php

use Illuminate\Config\Repository as Config;
use Illuminate\Filesystem\Filesystem;
use PHPGit\Git;

class GitCodex implements CodexInterface
{
	/**
	* The filesystem implementation.
	*
	* @var Filesystem
	*/
	protected $files;

	/**
	 * The config implementation.
	 *
	 * @var Config
	 */
	protected $config;

	/**
	 * The Git implementation.
	 *
	 * @var Git
	 */
	protected $git;

	/**
	 * Storage path.
	 *
	 * @var string
	 */
	protected $storagePath;

	/**
	 * Create a new codex instance.
	 *
	 * @param Config $config
	 * @param Filesystem $files
	 * @param Git $git
	 */
	public function __construct(Config $config, Filesystem $files, Git $git)
	{
		$this->config = $config;
		$this->files  = $files;
		$this->git    = $git;

		$this->storagePath = $this->config->get('codex.storage_path');
	}

	/**
	* Get the documentation table of contents page.
	*
	* @param  string $version
	* @return string
	*/
	public function getToc($manual, $version)
	{
		$tocFile = $this->storagePath.'/'.$manual.'/toc.md';
		$this->git->setRepository($this->storagePath.'/'.$manual);
		$this->git->checkout($version);

		if ($this->files->exists($tocFile)) {
			return Markdown::parse($this->files->get($tocFile), $manual.'/'.$version);
		} else {
			return null;
		}
	}

	/**
	* Get the given documentation page.
	*
	* @param  string $manual
	* @param  string $version
	* @param  string $page
	* @return string
	*/
	public function get($manual, $version, $page)
	{
		$page = $this->storagePath.'/'.$manual.'/'.$page.'.md';
		$this->git->setRepository($this->storagePath.'/'.$manual);
		$this->git->checkout($version);

		if ($this->files->exists($page)) {
			return Markdown::parse($this->files->get($page), $manual.'/'.$version);
		} else {
			App::abort(404);
		}
	}

	/**
	 * Gets the given documentation page modification time.
	 *
	 * @param  string $manual
	 * @param  string $version
	 * @param  string $page
	 * @return mixed
	 */
	public function getUpdatedTimestamp($manual, $version, $page)
	{
		$page = $this->storagePath.'/'.$manual.'/'.$page.'.md';
		$this->git->setRepository($this->storagePath.'/'.$manual);
		$this->git->checkout($version);

		if ($this->files->exists($page)) {
			$timestamp = DateTime::createFromFormat('U', filemtime($page));

			return $timestamp->format($this->config->get('codex.modified_timestamp'));
		} else {
			return false;
		}
	}

	/**
	 * Get all manuals from documentation directory.
	 *
	 * @return array
	 */
	public function getManuals()
	{
		return $this->getDirectories($this->storagePath);
	}

	/**
	 * Get all versions for the given manual.
	 *
	 * @param  string $manual
	 * @return array
	 */
	public function getVersions($manual)
	{
		$manualDir = $this->storagePath.'/'.$manual;
		$this->git->setRepository($manualDir);

		return $this->git->tag();
	}

	/**
	 * Get the default manual.
	 *
	 * @return mixed
	 */
	public function getDefaultManual()
	{
		$manuals = $this->getManuals();

		if (count($manuals) > 1) {
			if ( ! is_null($this->config->get('codex.default_manual'))) {
				return $this->config->get('codex.default_manual');
			} else {
				return strval($manuals[0]);
			}
		} elseif (count($manuals) === 1) {
			return strval($manuals[0]);
		} else {
			return null;
		}
	}

	/**
	 * Get the default version for the given manual.
	 *
	 * @param  string $manual
	 * @return string
	 */
	public function getDefaultVersion($manual)
	{
		$versions = $this->getVersions($manual);

		return strval(max($versions));
	}

	/**
	 * Search manual for given string.
	 *
	 * @param  string $manual
	 * @param  string $version
	 * @param  string $needle
	 * @return array
	 */
	public function search($manual, $version, $needle = '')
	{
		$results   = [];
		$directory = $this->storagePath.'/'.$manual;
		$this->git->setRepository($directory);
		$this->git->checkout($version);
		$files     = preg_grep('/toc\.md$/', $this->files->allFiles($directory),
		 	PREG_GREP_INVERT);

		if ( ! empty($needle)) {
			foreach ($files as $file) {
				$haystack = file_get_contents($file);

				if (strpos(strtolower($haystack), strtolower($needle)) !== false) {
					$results[] = [
						'title' => $this->getPageTitle((string)$file),
						'url'   => str_replace([$this->config->get('codex.storage_path'), '.md'], '', (string)$file),
					];
				}
			}
		}

		return $results;
	}

	/**
	 * Return the first line of the supplied page. This will (or rather should)
	 * always be an <h1> tag.
	 *
	 * @param  string $page
	 * @return string
	 */
	private function getPageTitle($page)
	{
		$file  = fopen($page, 'r');
		$title = fgets($file);

		fclose($file);

		return $title;
	}

	/**
	 * Return an array of folders within the supplied path.
	 *
	 * @param  string $path
	 * @return array
	 */
	private function getDirectories($path)
	{
		if ( ! $this->files->exists($path)) {
			App::abort(404);
		}

		$directories = $this->files->directories($path);
		$folders     = [];

		if (count($directories) > 0) {

		}

		foreach ($directories as $dir) {
			$dir = str_replace('\\', '/', $dir);
			$folder    = explode('/', $dir);
			$folders[] = end($folder);
		}

		return $folders;
	}
}
