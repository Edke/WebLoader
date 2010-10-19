<?php

namespace WebLoader;

/**
 * WebFiles
 *
 * @author kraken
 */
class WebFiles {

    private $files = array(), $linked = array();


    /**
     * Add file and its depentents
     * @param string $name
     * @param null|string $path
     * @param null|string|array $depends
     */
    public function addFile($name, $path = null, $depends = null) {
	if (\key_exists($name, $this->files)) {
	    throw new \LogicException("File '$name' already exists");
	}

	if (\is_string($depends)) {
	    $depends = array($depends);
	}
	elseif(\is_null($depends)) {
	    $depends = array();
	}

	foreach ($depends as $dependent) {
	    if (!\key_exists($dependent, $this->files)) {
		throw new \LogicException("File '$name' depends on unknown file '$dependent'");
	    }
	}
	$this->files[$name] = (object) array('deps' => $depends, 'path' => $path);
	return $this;
    }


    /**
     * link files
     * @param mixed
     * @return this
     */
    public function link() {
	$args = \func_get_args();
	foreach ($args as $arg) {
	    if (\is_string($arg)) {
		$arg = array($arg);
	    }
	    foreach ($arg as $name) {
		if (!\key_exists($name, $this->files)) {
		    throw new \LogicException("File '$name' unknown to WebFiles");
		}
		if (!\in_array($name, $this->linked)) {
		    $this->linked[] = $name;
		}
	    }
	}
	return $this;
    }


    /**
     * Get array of paths for item with $name with dependancies
     * @param string $name
     * @return array
     */
    private function getDeps($name) {
	$paths = array();
	foreach ($this->files[$name]->deps as $dependant) {
	    $_paths = $this->getDeps($dependant);
	    if (count($_paths)) {
		$paths = \array_merge($paths, $_paths);
	    }
	}
	if ($this->files[$name]->path) {
	    $paths[] = $this->files[$name]->path;
	}
	return $paths;
    }


    /**
     * Get array of paths for all linked files
     * @return array
     */
    public function getLinkedFiles() {
	$paths = array();

	foreach ($this->linked as $name) {
	    $_paths = $this->getDeps($name);
	    if (count($_paths)) {
		$paths = \array_merge($paths, $_paths);
	    }
	}

	$paths = \array_unique($paths);
	return $paths;
    }


    /**
     * Get only array of css files for all linked files
     * @return array
     */
    public function getLinkedCssFiles() {
	return $this->filterFilesByExtension('css');
    }


    /**
     * Get only array of js files for all linked files
     * @return array
     */
    public function getLinkedJsFiles() {
	return $this->filterFilesByExtension('js');
    }


    /**
     * Filter array for files with $extension
     * @param string $extension
     * @return array
     */
    private function filterFilesByExtension($extension) {
	$paths = $this->getLinkedFiles();
	foreach ($paths as $key => $path) {
	    if (!\preg_match('#' . $extension . '$#', \strtolower($path))) {
		unset($paths[$key]);
	    }
	}
	return $paths;
    }
}

