<?php


namespace upgrade\builder;

/**
 * Class ZipArchiver
 * @package upgrade\builder
 */
class ZipArchiver implements ArchiverInterface
{
    public function __construct()
    {
        exec("unzip -h 2> /dev/null", $out, $result);
        if ($result !== 0) {
            throw new \Exception('unzip is not installed');
        }
    }

    /**
     * @inheritdoc
     */
    public function find($archive = '', $regex = '')
    {
        exec("unzip -Z1 {$archive} 2> /dev/null | grep \"{$regex}\\$\"", $output, $result);

        if ($result !== 0) {
            $output = array();
        }

        return $output;
    }

    /**
     * @inheritdoc
     */
    public function extract($archive = '', $extract_path = './', $files_list = array())
    {
        $files_list = implode(' ', (array) $files_list);
        exec("unzip -o {$archive} {$files_list} -d {$extract_path} 2>&1", $output, $result);

        return $result === 0;
    }
}