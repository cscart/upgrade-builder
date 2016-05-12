<?php


namespace upgrade\builder;


interface UpgradePackageInterface
{
    /**
     * Set files
     * @param array $files
     */
    public function setFiles(array $files);

    /**
     * Set migrations
     * @param array $migrations
     */
    public function setMigrations(array $migrations);

    /**
     * Set validators
     * @param array $validators
     */
    public function setValidators(array $validators);

    /**
     * Set post script
     * @param string $file
     */
    public function setPostScript($file);

    /**
     * Set pre script
     * @param string $file
     */
    public function setPreScript($file);

    /**
     * Set pre script extra code
     * @param string $code
     */
    public function setPreScriptExtraCode($code);

    /**
     * Set extra files
     * @param array $files
     * @param string $dir
     * @return mixed
     */
    public function setExtraFiles(array $files, $dir = "extra_files");

    /**
     * @param array $data
     * @return mixed
     */
    public function extendScheme(array $data);

    /**
     * Create archive package
     * @param string $file
     * @return bool
     */
    public function create($file);

    /**
     * Get package schema
     * @return array
     */
    public function getSchema();
}