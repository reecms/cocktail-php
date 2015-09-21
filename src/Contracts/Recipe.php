<?php

namespace Ree\Cocktail\Contracts;

/**
 * A cocktail recipe
 *
 * @author Hieu Le <hieu@codeforcevina.com>
 */
interface Recipe
{

    /**
     * Get the source directory
     * 
     * @return string path the the directory containing source files
     */
    public function getSourceDir();

    /**
     * Get the destination directory
     * 
     * @return string path to the directory containing compiled files
     */
    public function getBuildDir();

    /**
     * Get the import paths
     * 
     * @return array
     */
    public function getImportPaths();

    /**
     * Add new import paths
     * 
     * @param type $paths
     */
    public function addImportPaths($paths);
}
