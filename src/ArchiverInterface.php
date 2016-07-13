<?php


namespace upgrade\builder;

/**
 * Interface ArchiverInterface
 * @package upgrade\builder
 */
interface ArchiverInterface
{
    /**
     * Finds files in the archive
     *
     * @param string $archive Archive path
     * @param string $regex Regex to match files against
     *
     * @return array Found files' paths
     */
    public function find($archive = '', $regex = '');

    /**
     * Extracts files from the archive
     *
     * @param string $archive Archive path
     * @param string $extract_path Directory to extract files
     * @param array $files_list Files to extract
     *
     * @return bool True on success
     */
    public function extract($archive = '', $extract_path = './', $files_list = array());
}