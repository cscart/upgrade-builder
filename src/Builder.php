<?php

namespace upgrade\builder;

/**
 * Class Builder
 * @package upgrade\builder
 */
class Builder
{
    /**
     * Product name (addon_name, edition_name)
     * @var string
     */
    protected $product_name;

    /**
     * Initial archive object
     * @var ReleaseArchiveInterface
     */
    protected $archive_from;

    /**
     * Final archive object
     * @var ReleaseArchiveInterface
     */
    protected $archive_to;

    /**
     * Working directory
     * @var string
     */
    protected $work_directory_path;

    /**
     * Path to validators folder
     * @var string
     */
    protected $validators_path;

    /**
     * Path to pre|post scripts folder
     * @var string
     */
    protected $scripts_path;

    /**
     * Path to migrations folder
     * @var string
     */
    protected $migrations_path;

    /**
     * Path to extra file
     * @var string
     */
    protected $extra_path;

    /**
     * Path to extra files folder
     * @var string
     */
    protected $extra_files_path;

    /**
     * Path to unpacked archive from
     * @var string
     */
    protected $unpacked_archive_from_path;

    /**
     * Path to unpacked archive to
     * @var string
     */
    protected $unpacked_archive_to_path;

    /**
     * Initial version
     * @var string
     */
    protected $version_from;

    /**
     * Final version
     * @var string
     */
    protected $version_to;

    /**
     * Exclude files
     * @var array
     */
    protected $exclude_files = array();

    /**
     * Exclude file extensions
     * @var array
     */
    protected $exclude_extensions = array();

    /**
     * @var UpgradePackageInterface
     */
    protected $package;

    /**
     * Constructor
     *
     * @param ReleaseArchiveInterface $archive_from Initial release
     * @param ReleaseArchiveInterface $archive_to Final release
     * @param string $working_directory Path to working directory
     * @param UpgradePackageInterface|null $package UpgradePackage object
     * @throws \Exception
     */
    public function __construct(ReleaseArchiveInterface $archive_from, ReleaseArchiveInterface $archive_to, $working_directory, UpgradePackageInterface $package = null)
    {
        if ($archive_from->getName() != $archive_to->getName()) {
            throw new \Exception("Product name not equal.");
        }

        if (version_compare($archive_to->getVersion(), $archive_from->getVersion()) < 0) {
            throw new \Exception("Invalid versions.");
        }

        $this->archive_from = $archive_from;
        $this->archive_to = $archive_to;
        $this->product_name = $archive_to->getName();
        $this->version_from = $archive_from->getVersion();
        $this->version_to = $archive_to->getVersion();

        $this->setWorkDirectoryPath($working_directory);

        if ($package === null) {
            $package = new UpgradePackage($this->work_directory_path . '/upgrade');
        }

        $this->package = $package;
    }

    /**
     * Set work directory path
     *
     * @param string $path
     * @throws \Exception
     */
    protected function setWorkDirectoryPath($path)
    {
        $path = $this->preparePath($path);

        if (empty($path) || !is_readable($path)) {
            throw new \Exception('Invalid path for work_directory_path');
        }

        $this->work_directory_path = $path;
        $this->unpacked_archive_from_path = $this->work_directory_path . '/from';
        $this->unpacked_archive_to_path = $this->work_directory_path . '/to';

        $this->exec('rm -rf ' . $this->work_directory_path);
        $this->mkdir($this->work_directory_path . '/upgrade');
        $this->mkdir($this->work_directory_path . '/upgrade/package');

        if (!$this->mkdir($this->unpacked_archive_from_path)) {
            throw new \Exception("Unable to create directory: {$this->unpacked_archive_from_path}");
        }

        if (!$this->mkdir($this->unpacked_archive_to_path)) {
            throw new \Exception("Unable to create directory: {$this->unpacked_archive_from_path}");
        }
    }

    /**
     * Set path to validators folder
     *
     * @param string $path
     */
    public function setValidatorsPath($path)
    {
        $path = $this->preparePath($path);
        $this->validators_path = $path;
    }

    /**
     * Set path to scripts folder
     *
     * @param string $path
     */
    public function setScriptsPath($path)
    {
        $path = $this->preparePath($path);
        $this->scripts_path = $path;
    }

    /**
     * Set path to migrations folder
     *
     * @param string $path
     */
    public function setMigrationsPath($path)
    {
        $path = $this->preparePath($path);
        $this->migrations_path = $path;
    }

    /**
     * Set path to extra file
     *
     * @param string $path
     */
    public function setExtraPath($path)
    {
        $path = $this->preparePath($path);
        $this->extra_path = $path;
    }

    /**
     * Set path to extra files folder
     *
     * @param string $path
     */
    public function setExtraFilesPath($path)
    {
        $path = $this->preparePath($path);
        $this->extra_files_path = $path;
    }

    /**
     * Set exclude files. Files will not be included in the upgrade package
     *
     * @param array $files array of files
     * @param array|null $files_on_delete If is null, for delete used $files
     */
    public function setExcludeFiles(array $files, array $files_on_delete = null)
    {
        if ($files_on_delete === null) {
            $this->exclude_files['changed'] = $files;
            $this->exclude_files['deleted'] = $files;
        } else {
            $this->exclude_files['changed'] = $files;
            $this->exclude_files['deleted'] = $files_on_delete;
        }
    }

    /**
     * Set exclude file extensions. Files with these extensions will not be included in the upgrade package
     *
     * @param array $extensions array of file extensions
     * @param array|null $extensions_on_delete If is null, for delete used $extensions
     */
    public function setExcludeExtensions(array $extensions, array $extensions_on_delete = null)
    {
        if ($extensions_on_delete === null) {
            $this->exclude_extensions['changed'] = $extensions;
            $this->exclude_extensions['deleted'] = $extensions;
        } else {
            $this->exclude_extensions['changed'] = $extensions;
            $this->exclude_extensions['deleted'] = $extensions_on_delete;
        }
    }

    /**
     * Run build package
     *
     * @throws \Exception
     */
    public function run()
    {
        $this->message('Unpack release archives into working directory');
        $this->unpackArchives();

        if (!empty($this->validators_path) && !is_dir($this->validators_path)) {
            $this->message("Validators: Directory {$this->validators_path} not found.");
        }

        if (!empty($this->migrations_path) && !is_dir($this->migrations_path)) {
            $this->message("Migrations: Directory {$this->migrations_path} not found.");
        }

        if (!empty($this->extra_path) && !file_exists($this->extra_path)) {
            $this->message("Extra file: Path {$this->extra_path} not found.");
        }

        if (!empty($this->extra_files_path) && !is_dir($this->extra_files_path)) {
            $this->message("Extra files: Directory {$this->extra_files_path} not found.");
        }

        if (!empty($this->scripts_path) && !is_dir($this->scripts_path)) {
            $this->message("Scripts files: Directory {$this->scripts_path} not found.");
        }

        $this->message('Init upgrade package');

        $this->initPackage();

        $this->message('Create package');
        $this->createPackage();

        $this->message('Remove temp data');
        $this->exec('rm -rf ' . $this->unpacked_archive_from_path);
        $this->exec('rm -rf ' . $this->unpacked_archive_to_path);

        $this->message('Done');
    }

    /**
     * Init package
     *
     * @throws \Exception
     */
    protected function initPackage()
    {
        $this->message('Get files');
        $this->package->setFiles($this->getFiles());

        $this->message('Get validators');
        $this->package->setValidators($this->getValidators());

        $this->message('Get migrations');
        $this->package->setMigrations($this->getMigrations());

        $this->message('Get extra files');
        $this->package->setExtraFiles($this->getExtraFiles());

        $this->message('Get extra package data');
        $this->package->extendScheme($this->getExtraPackageData());

        $this->message('Get pre script');
        $this->package->setPreScript($this->getPreScriptFile());

        $this->message('Get post script');
        $this->package->setPostScript($this->getPostScriptFile());
    }

    /**
     * Create archive upgrade package
     */
    protected function createPackage()
    {
        $this->package->create($this->work_directory_path . '/' . $this->getPackageName());
    }

    /**
     * Unpack archive to|from file
     *
     * @throws \Exception
     */
    public function unpackArchives()
    {
        if (!$this->archive_from->extractTo($this->unpacked_archive_from_path)) {
            throw new \Exception("Unable to extract release archive.");
        }

        if (!$this->archive_to->extractTo($this->unpacked_archive_to_path)) {
            throw new \Exception("Unable to extract release archive.");
        }
    }

    /**
     * Get changed/created/deleted files
     *
     * ```php
     * [
     *  'relative_path' => [
     *      'src' => 'source',
     *      'status' => 'deleted|new|change',
     *      'hash' => 'md5'
     *  ],
     *  ...
     * ]
     * ```
     * @return array
     */
    public function getFiles()
    {
        return array_merge(
            $this->getChangedFiles(),
            $this->getDeletedFiles()
        );
    }

    /**
     * Return archive name
     *
     * @return string
     */
    public function getPackageName()
    {
        return "upgrade_{$this->version_from}_{$this->product_name}-{$this->version_to}_{$this->product_name}.zip";
    }

    /**
     * Execute command
     *
     * @param string $command
     * @return string
     */
    protected function exec($command)
    {
        return exec($command);
    }

    /**
     * Output message
     *
     * @param $message
     */
    protected function message($message)
    {
        echo $message . "\n";
    }

    /**
     * Create directory
     *
     * @param string $path
     * @param int $mode
     * @param bool|true $recursive
     * @return bool
     */
    protected function mkdir($path, $mode = 0777, $recursive = true)
    {
        return mkdir($path, $mode, $recursive);
    }

    /**
     * Get pre script file
     * @return string|bool
     */
    public function getPreScriptFile()
    {
        if (!empty($this->scripts_path) && is_dir($this->scripts_path)) {
            $scripts = scandir($this->scripts_path);

            foreach ($scripts as $value) {
                if (strpos($value, 'pre_') === 0) {
                    return $this->scripts_path . '/' . $value;
                }
            }
        }

        return false;
    }

    /**
     * Get post script file
     * @return string|bool
     */
    public function getPostScriptFile()
    {
        if (!empty($this->scripts_path) && is_dir($this->scripts_path)) {
            $scripts = scandir($this->scripts_path);

            foreach ($scripts as $value) {
                if (strpos($value, 'post_') === 0) {
                    return $this->scripts_path . '/' . $value;
                }
            }
        }

        return false;
    }

    /**
     * Return changed files
     * @return array
     */
    protected function getChangedFiles()
    {
        return $this->getRecursiveChangedFiles($this->unpacked_archive_to_path);
    }

    /**
     * Check recursive changed files
     * @param string $source path
     * @return array
     */
    protected function getRecursiveChangedFiles($source)
    {
        $result = array();

        if (is_file($source)) {
            $old_source = str_replace($this->unpacked_archive_to_path, $this->unpacked_archive_from_path, $source);

            if (is_file($old_source)) {
                $md5 = md5_file($old_source);

                if ($md5 != md5_file($source)) {
                    $path = $this->getRelativePath($old_source, $this->unpacked_archive_from_path);

                    if ($this->isExcludedPath($path, 'changed')) {
                        return array();
                    }

                    $result[$path] = array(
                        'src' => $source,
                        'status' => 'changed',
                        'hash' => $md5,
                    );
                }
            } else {
                $path = $this->getRelativePath($old_source, $this->unpacked_archive_from_path);

                if ($this->isExcludedPath($path, 'changed')) {
                    return array();
                }

                $result[$path] = array(
                    'src' => $source,
                    'status' => 'new'
                );
            }
        } elseif (is_dir($source)) {
            $dir = dir($source);

            while (false !== $entry = $dir->read()) {
                if ($entry == '.' || $entry == '..' || $entry == '.svn' || $entry == '.git') {
                    continue;
                }

                $result = array_merge(
                    $result,
                    $this->getRecursiveChangedFiles($source . '/' . $entry)
                );
            }

            $dir->close();
        }

        return $result;
    }

    /**
     * Return deleted files
     * @return array
     */
    protected function getDeletedFiles()
    {
        return $this->getRecursiveDeletedFiles($this->unpacked_archive_from_path);
    }

    /**
     * Check recursive file deleted
     * @param string $source
     * @return array
     */
    protected function getRecursiveDeletedFiles($source)
    {
        $result = array();

        if (is_file($source)) {
            $new_source = str_replace($this->unpacked_archive_from_path, $this->unpacked_archive_to_path, $source);

            if (!is_file($new_source)) {
                $path = $this->getRelativePath($source, $this->unpacked_archive_from_path);

                if ($this->isExcludedPath($path, 'deleted')) {
                    return array();
                }

                $result[$path] = array(
                    'src' => $source,
                    'status' => 'deleted',
                    'hash' => md5_file($source),
                );
            }
        } elseif (is_dir($source)) {
            $dir = dir($source);

            while (false !== $entry = $dir->read()) {
                if ($entry == '.' || $entry == '..' || $entry == '.svn' || $entry == '.git') {
                    continue;
                }

                $result = array_merge(
                    $result,
                    $this->getRecursiveDeletedFiles($source . '/'. $entry)
                );
            }

            $dir->close();
        }

        return $result;
    }

    /**
     * Check path on excluded
     * @param string $path
     * @param string $mode deleted|changed
     * @return bool
     */
    protected function isExcludedPath($path, $mode)
    {
        $files = isset($this->exclude_files[$mode]) ? $this->exclude_files[$mode] : array();
        $extensions = isset($this->exclude_extensions[$mode]) ? $this->exclude_extensions[$mode] : array();

        foreach ($files as $file) {
            if (stripos($path, $file) !== false) {
                return true;
            }
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (in_array($ext, $extensions)) {
            return true;
        }

        return false;
    }

    /**
     * Check migration on excluded
     * @param string $migration
     * @return bool
     */
    protected function isExcludedMigration($migration)
    {
        return false;
    }


    /**
     * Check validator on excluded
     * @param string $path
     * @return bool
     */
    protected function isExcludedValidator($path)
    {
        return false;
    }

    /**
     * Updated path
     * @param string $path
     * @param string $folder_from
     * @return string
     */
    protected function getRelativePath($path, $folder_from)
    {
        return str_replace(rtrim($folder_from, '/') . '/', '', $path);
    }

    /**
     * Return array of migrations
     * @return array
     */
    public function getMigrations()
    {
        $result = array();

        if (!empty($this->migrations_path) && is_dir($this->migrations_path)) {
            $migrations = scandir($this->migrations_path);

            foreach ($migrations as $value) {
                if ($value == '.' || $value == '..' || !preg_match('/[0-9]{5,}.*\.php/i', $value)) {
                    continue;
                }

                if ($this->isExcludedMigration($value)) {
                    continue;
                }

                $result[] = $this->migrations_path . '/' . $value;
            }
        }

        return $result;
    }

    /**
     * Return array of validators
     * @return array
     */
    public function getValidators()
    {
        $result = array();

        if (!empty($this->validators_path) && is_dir($this->validators_path)) {
            $items = scandir($this->validators_path);

            foreach ($items as $value) {
                if ($value == '.' || $value == '..') {
                    continue;
                }

                if ($this->isExcludedValidator($value)) {
                    continue;
                }

                $result[] = $this->validators_path . '/' . $value;
            }
        }

        return $result;
    }

    /**
     * Return extra package data
     *
     * @return array
     * @throws \Exception
     */
    public function getExtraPackageData()
    {
        $result = array();

        if (!empty($this->extra_path) && file_exists($this->extra_path)) {
            $result = include($this->extra_path);

            if (!is_array($result)) {
                throw new \Exception('Extra package data must be array');
            }
        }

        return $result;
    }

    /**
     * Return array of extra files
     * @return array
     */
    public function getExtraFiles()
    {
        $result = array();

        if (!empty($this->extra_files_path) && is_dir($this->extra_files_path)) {
            $items = scandir($this->extra_files_path);

            foreach ($items as $value) {
                if ($value == '.' || $value == '..') {
                    continue;
                }

                $result[] = $this->extra_files_path . '/' . $value;
            }
        }

        return $result;
    }

    /**
     * Convert underscored string to CamelCase
     * @param string $string
     * @return string
     */
    protected function camelize($string)
    {
        return preg_replace_callback('/_(.?)/', function($matches) {
            return strtoupper($matches[1]);
        }, $string);
    }

    /**
     * Rrepare path
     * @param string $path
     * @return string
     */
    protected function preparePath($path)
    {
        return strtr($path, array(
            '#VERSION_FROM#' => $this->version_from,
            '#VERSION_TO#' => $this->version_to,
            '#NAME#' => $this->product_name
        ));
    }

    /**
     * Get path when archive_from unpacked
     * @return string
     */
    public function getUnpackedArchiveFromPath()
    {
        return $this->unpacked_archive_from_path;
    }

    /**
     * Get path when archive_to unpacked
     * @return string
     */
    public function getUnpackedArchiveToPath()
    {
        return $this->unpacked_archive_to_path;
    }

    /**
     * Get package
     * @return UpgradePackageInterface
     */
    public function getPackage()
    {
        return $this->package;
    }

    /**
     * Get product name
     * @return string
     */
    public function getProductName()
    {
        return $this->product_name;
    }
}