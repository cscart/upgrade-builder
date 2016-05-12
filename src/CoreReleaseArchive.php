<?php


namespace upgrade\builder;

/**
 * Class CoreReleaseArchive
 * @package upgrade\builder
 */
class CoreReleaseArchive extends ReleaseArchive
{
    /**
     * CoreReleaseArchive constructor.
     * @param string $file
     * @throws \Exception
     */
    public function __construct($file)
    {
        parent::__construct($file, null, null);

        $config = $this->getConfig();
        $this->name = strtolower($config['name']);
        $this->version = $config['version'];

        if ($this->name == 'multivendor') {
            $this->name = 'mve';
        }
    }

    /**
     * Extract config.php and array of configuration
     * @return array
     * @throws \Exception
     */
    protected function getConfig()
    {
        $dir = sys_get_temp_dir();
        $files = array(
            './config.php',
            'config.php'
        );
        $file = null;

        foreach ($files as $file) {
            exec("tar -xf {$this->file} -C {$dir} {$file}> /dev/null 2>&1", $output, $result);

            if ($result === 0) {
                $file = $dir . '/config.php';
                break;
            }
        }

        if (!is_file($file)) {
            throw new \Exception("Unable to extract config.php");
        }

        $content = file_get_contents($file);
        unlink($file);
        $result = array(
            'version' => null,
            'name' => null
        );

        if (preg_match('/PRODUCT_VERSION\'.*?\'(.*?)\'/ims', $content, $matches)) {
            $result['version'] = $matches[1];
        }

        if (preg_match('/PRODUCT_EDITION\'.*?\'(.*?)\'/ims', $content, $matches)) {
            $result['name'] = $matches[1];
        }

        return $result;
    }
}