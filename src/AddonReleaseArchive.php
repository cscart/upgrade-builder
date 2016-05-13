<?php


namespace upgrade\builder;

/**
 * Class AddonReleaseArchive
 * @package upgrade\builder
 */
class AddonReleaseArchive extends ReleaseArchive
{
    /**
     * CoreReleaseArchive constructor.
     * @param string $file
     * @throws \Exception
     */
    public function __construct($file)
    {
        parent::__construct($file, null, null);

        $scheme = $this->getScheme();

        $this->name = (string) $scheme->id;
        $this->version = (string) $scheme->version;
    }

    /**
     * Get addon scheme
     * @return \SimpleXMLElement
     * @throws \Exception
     */
    protected function getScheme()
    {
        $dir = sys_get_temp_dir();

        exec("tar -tf {$this->file} 2> /dev/null | grep -m 2 \"app/addons/.*/addon.xml\\$\"", $output, $result);

        if ($result !== 0) {
            throw new \Exception("Not found addon.xml");
        }


        $file = array_pop($output);

        exec("tar -xf {$this->file} -C {$dir} {$file}> /dev/null 2>&1", $output, $result);
        $file = realpath($dir . '/' . $file);

        if ($result !== 0 || !is_file($file)) {
            throw new \Exception("Unable to extract addon.xml");
        }

        $xml = simplexml_load_file($file);

        if ($xml === false) {
            throw new \Exception("Unable to load addon.xml");
        }

        return $xml;
    }
}