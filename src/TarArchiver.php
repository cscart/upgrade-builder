<?php


namespace upgrade\builder;

/**
 * Class TarArchiver
 * @package upgrade\builder
 */
class TarArchiver implements ArchiverInterface
{
    public function __construct()
    {
        exec("tar --version 2> /dev/null", $out, $result);
        if ($result !== 0) {
            throw new \Exception('tar is not installed');
        }
    }

    /**
     * @inheritdoc
     */
    public function find($archive = '', $regex = '')
    {
        exec("tar -tf {$archive} 2> /dev/null | grep \"{$regex}\\$\"", $output, $result);

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
        $flags = $this->getExtractFlags($archive);
        $files_list = implode(' ', (array) $files_list);
        exec("tar {$flags} {$archive} -C {$extract_path} {$files_list} 2>&1", $output, $result);

        return $result === 0;
    }

    /**
     * Provides extract flags for tar command
     *
     * @param string $archive Path to archive
     * @return string Flags
     */
    public function getExtractFlags($archive)
    {
        $flags = '--overwrite -x';
        if (substr(strtolower($archive), -2) === 'gz') {
            $flags .= 'z';
        }
        $flags .= 'f';

        return $flags;
    }
}