<?php


namespace upgrade\builder;

/**
 * Interface ReleaseArchiveInterface
 * @package upgrade\builder
 */
interface ReleaseArchiveInterface
{
    /**
     * Get product name
     * @return string
     */
    public function getName();

    /**
     * Get archive path
     * @return string
     */
    public function getPath();

    /**
     * Get version
     * @return string
     */
    public function getVersion();

    /**
     * Extract archive to directory
     * @param string $path
     * @return bool
     */
    public function extractTo($path);
}