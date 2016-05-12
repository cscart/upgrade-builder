<?php


namespace upgrade\builder;

/**
 * Class ReleaseArchive
 * @package upgrade\builder
 */
class ReleaseArchive implements ReleaseArchiveInterface
{
    /**
     * Archive file
     * @var string
     */
    protected $file;

    /**
     * Edition
     * @var string|null
     */
    protected $name = null;

    /**
     * Version
     * @var string|null
     */
    protected $version = null;

    /**
     * ReleaseArchive constructor.
     * @param string $file
     * @param string $name
     * @param string $version
     * @throws \Exception
     */
    public function __construct($file, $name, $version)
    {
        if (!is_file($file)) {
            throw new \Exception('File not found.');
        }

        $this->name = $name;
        $this->version = $version;
        $this->file = $file;
    }
    /**
     * Get product name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get archive path
     * @return string
     */
    public function getPath()
    {
        return $this->file;
    }

    /**
     * Get version
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Extract archive to directory
     * @param string $path
     * @return bool
     */
    public function extractTo($path)
    {
        exec("tar -xzvf {$this->file} -C {$path} 2>/dev/null", $output, $result);

        return $result === 0;
    }
}