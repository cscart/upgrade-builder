<?php


namespace upgrade\builder;

/**
 * Class CoreBuilder
 * @package upgrade\builder
 */
class CoreBuilder extends Builder
{
    /**
     * Array of exclude addons
     * @var array
     */
    protected $exclude_addons = array();

    /**
     * Set exclude addons
     * ```php
     * $addons = [
     *  'addon_name',
     *  'addon_name2' => [
     *      'allowed_file1',
     *      'allowed_file2',
     *   ]
     * ]
     * ```
     * @param array $addons
     */
    public function setExcludeAddons(array $addons)
    {
        $this->exclude_addons = $addons;
    }

    /**
     * @inheritdoc
     */
    protected function isExcludedMigration($migration)
    {
        if (!parent::isExcludedMigration($migration)) {
            // Check edition and copy only appropriate migrations
            $is_mve = preg_match('_mve_', $migration);
            $is_ult = preg_match('_ult_', $migration);

            if (strtoupper($this->product_name) == 'MVE' && $is_ult) {
                return true;
            }

            if (strtoupper($this->product_name) == 'ULTIMATE' && $is_mve) {
                return true;
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    protected function isExcludedPath($path, $mode)
    {
        if (!parent::isExcludedPath($path, $mode)) {
            $addons = $this->exclude_addons;

            foreach ($addons as $key => $item) {
                $allowed_files = array();

                if (is_array($item)) {
                    $addon = $key;
                    $allowed_files = $item;
                } else {
                    $addon = $item;
                }

                $addon_paths = array(
                    'addons/' . $addon . '/',
                    'addons/' . $addon . '.po',
                    'images/' . $addon . '/',
                );

                foreach ($addon_paths as $addon_path) {
                    if (stripos($path, $addon_path) !== false) {
                        foreach ($allowed_files as $file) {
                            if (stripos($path, $file) !== false) {
                                return false;
                            }
                        }

                        return true;
                    }
                }
            }

            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function getRelativePath($path, $folder_from)
    {
        $result = parent::getRelativePath($path, $folder_from);

        if (strpos($result, 'design/themes/') === 0) {
            $result = str_replace('design/themes/', 'var/themes_repository/', $result);
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    protected function initPackage()
    {
        parent::initPackage();
        $schema = $this->package->getSchema();

        // Patch pre script. Add extra eval code
        $code = <<<EOT

// Get extra fixes/secure updates/etc.
\$hot_updates = fn_get_contents(\Tygh\Registry::get('config.resources.updates_server') . '/index.php?dispatch=product_updates.get_fh_code');
\$hot_updates = base64_decode(\$hot_updates);
eval(\$hot_updates);
EOT;
        if (isset($schema['files']['upgrades/source_restore.php'])) {
            $this->package->setExtraFiles(array("{$this->unpacked_archive_to_path}/upgrades/source_restore.php"), 'extra');

            $code .= <<<EOT

// We modifed the source_restore.php script. Process it manually before the migrations
fn_copy(\$content_path . 'extra/source_restore.php', \$this->config['dir']['root'] . '/upgrades/source_restore.php');

\$restore_preparation_result = \$this->prepareRestore(
    \$package_id, \$schema, \$information_schema, \$backup_filename . '.zip'
);

if (!\$restore_preparation_result) {
    \$logger->add('Upgrade stopped: Unable to prepare restore file. restore.php was locally modified/removed or renamed.');
    return array(false, array(__('restore') => __('upgrade_center.unable_to_prepare_restore')));
}

list(\$restore_key, \$restore_file_path, \$restore_http_path) = \$restore_preparation_result;
EOT;
        }

        $this->package->setPreScriptExtraCode($code);
    }

    /**
     * @inheritdoc
     */
    public function unpackArchives()
    {
        parent::unpackArchives();

        $items = array('images', '.htaccess', 'config.local.php', 'install', 'database');
        $files = array();

        foreach ($items as $item) {
            $files[] = $this->unpacked_archive_to_path . '/' . $item;
            $files[] = $this->unpacked_archive_from_path . '/' . $item;
        }

        foreach ($files as $file) {
            if (is_file($file) || is_dir($file)) {
                $this->exec('rm -rf ' . $file);
            }
        }
    }
}