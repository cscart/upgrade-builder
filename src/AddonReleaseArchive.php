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

        $found_files = $this->archiver->find($this->file, 'app/addons/.*/addon.xml');
        if (empty($found_files)) {
            throw new \Exception("Not found addon.xml");
        }

        sort($found_files);
        $file = reset($found_files);

        $ok = $this->archiver->extract($this->file, $dir, $file);
        $file = realpath($dir . '/' . $file);

        if (!$ok || !is_file($file)) {
            throw new \Exception("Unable to extract addon.xml");
        }

        $xml = simplexml_load_file($file);

        if ($xml === false) {
            throw new \Exception("Unable to load addon.xml");
        }

        return $xml;
    }
}
