<?php


namespace upgrade\builder;

/**
 * Class UpgradePackage
 * @package upgrade\builder
 */
class UpgradePackage implements UpgradePackageInterface
{
    /**
     * @var array
     */
    protected $schema = array();

    /**
     * @var string
     */
    protected $work_directory_path;

    /**
     * @var string
     */
    protected $pre_script_code;

    /**
     * UpgradePackage constructor.
     * @param $work_directory_path
     * @throws \Exception
     */
    public function __construct($work_directory_path)
    {
        if (empty($work_directory_path) || !is_readable($work_directory_path)) {
            throw new \Exception('Invalid path for work_directory_path');
        }

        $this->work_directory_path = $work_directory_path;
    }

    /**
     * @inheritDoc
     */
    public function setFiles(array $files)
    {
        foreach ($files as $path => $data) {
            $src = $data['src'];
            unset($data['src']);

            $this->schema['files'][$path] = $data;

            if ($data['status'] == 'deleted') {
                continue;
            }

            $this->exec("mkdir -p {$this->work_directory_path}/package/" . escapeshellarg(dirname($path)));
            $this->exec("cp " . escapeshellarg($src) . " {$this->work_directory_path}/package/" . escapeshellarg($path));
        }
    }

    /**
     * @inheritDoc
     */
    public function setMigrations(array $migrations)
    {
        if (!empty($migrations)) {
            $this->exec("mkdir -p {$this->work_directory_path}/migrations/");

            foreach ($migrations as $migration) {
                $this->schema['migrations'][] = basename($migration);
                $this->exec("cp " . escapeshellarg($migration) . " {$this->work_directory_path}/migrations/");
           }
        }
    }

    /**
     * @inheritDoc
     */
    public function setValidators(array $validators)
    {
        if (!empty($validators)) {
            $this->exec("mkdir -p {$this->work_directory_path}/validators/");

            foreach ($validators as $validator) {
                $this->schema['validators'][] = basename($validator, '.php');
                $this->exec("cp " . escapeshellarg($validator) . " {$this->work_directory_path}/validators/");
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function setPostScript($file)
    {
        if (!empty($file)) {
            $this->exec("mkdir -p {$this->work_directory_path}/scripts/");
            $this->schema['scripts']['post'] = basename($file);
            $this->exec("cp " . escapeshellarg($file) . " {$this->work_directory_path}/scripts/");
        }
    }

    /**
     * @inheritDoc
     */
    public function setPreScript($file)
    {
        if (!empty($file)) {
            $this->exec("mkdir -p {$this->work_directory_path}/scripts/");
            $this->schema['scripts']['pre'] = basename($file);
            $this->exec("cp " . escapeshellarg($file) . " {$this->work_directory_path}/scripts/");
        }
    }

    /**
     * @inheritDoc
     */
    public function setPreScriptExtraCode($code)
    {
        $this->pre_script_code = $code;
    }

    /**
     * @inheritDoc
     */
    public function setExtraFiles(array $files, $dir = "extra_files")
    {
        if (!empty($files)) {
            $this->exec("mkdir -p {$this->work_directory_path}/{$dir}/");

            foreach ($files as $file) {
                $this->exec("cp -r " . escapeshellarg($file) . " {$this->work_directory_path}/{$dir}/");
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function extendScheme(array $data)
    {
        if (!empty($data['files'])) {
            $this->setFiles($data['files']);
            unset($data['files']);
        }

        $this->schema = array_merge_recursive($this->schema, $data);
    }

    /**
     * @inheritDoc
     */
    public function create($file)
    {
        if (!empty($this->pre_script_code)) {
            if (empty($this->schema['scripts']['pre'])) {
                $this->exec("mkdir -p {$this->work_directory_path}/scripts/");
                $this->schema['scripts']['pre'] = 'pre_script.php';
                file_put_contents($this->work_directory_path . '/scripts/pre_script.php', "<?php\n\n");
            }

            $pre_script = $this->work_directory_path . '/scripts/' . $this->schema['scripts']['pre'];

            $script = preg_replace('/<\?(php)?/', "<?php\n" . $this->pre_script_code . "\n\n", file_get_contents($pre_script), 1);
            file_put_contents($pre_script, $script);
        }

        file_put_contents("{$this->work_directory_path}/package.json", json_encode($this->schema, JSON_PRETTY_PRINT));

        $this->exec("cd {$this->work_directory_path}/; zip -r {$file} ./* 2>/dev/null");

        return $file;
    }

    /**
     * @inheritDoc
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * Execute command
     * @param string $command
     * @return string
     */
    protected function exec($command)
    {
        return exec($command);
    }
}