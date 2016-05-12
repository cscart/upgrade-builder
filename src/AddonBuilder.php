<?php


namespace upgrade\builder;

/**
 * Class AddonBuilder
 *
 * @package upgrade\builder
 */
class AddonBuilder extends Builder
{
    /**
     * Get path to upgrade directory on addon
     * @return string
     */
    public function getAddonUpgradePath()
    {
        return $this->getUnpackedArchiveToPath() . '/app/addons/' . $this->product_name . '/upgrades/' . $this->version_to;
    }

    /**
     * Init addon upgrade paths
     */
    public function initPaths()
    {
        $this->setMigrationsPath($this->getAddonUpgradePath() . '/migrations');
        $this->setValidatorsPath($this->getAddonUpgradePath() . '/validators');
        $this->setExtraPath($this->getAddonUpgradePath() . '/extra/extra.php');
        $this->setExtraFilesPath($this->getAddonUpgradePath() . '/extra_files');
        $this->setScriptsPath($this->getAddonUpgradePath() . '/scripts');
    }
}