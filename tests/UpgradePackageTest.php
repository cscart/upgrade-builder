<?php

/**
 * Class UpgradePackageTest
 */
class UpgradePackageTest extends PHPUnit_Framework_TestCase
{

    public function tearDown()
    {
        parent::tearDown();

        exec("rm -rf " . __DIR__ . '/data/runtime/upgrade');
    }

    public function setUp()
    {
        parent::setUp();

        exec("mkdir " . __DIR__ . '/data/runtime/upgrade');
    }

    public function testSchema()
    {
        $package = new \upgrade\builder\UpgradePackage(__DIR__ . '/data/runtime/upgrade');

        $package->setMigrations(array(
            __DIR__ . '/data/upgrade/migrations/62312332434_mve_migration3.php',
            __DIR__ . '/data/upgrade/migrations/62312332434_test_migration1.php',
            __DIR__ . '/data/upgrade/migrations/62312332434_test_migration2.php'
        ));

        $package->setValidators(array(
            __DIR__ . '/data/upgrade/validators/validator1.php',
            __DIR__ . '/data/upgrade/validators/validator2.php',
        ));

        $package->setPreScript(__DIR__ . '/data/upgrade/scripts/pre_script.php');
        $package->setPostScript(__DIR__ . '/data/upgrade/scripts/post_script.php');
        $package->setFiles(array(
            'test.php' => array(
                'status' => 'new',
                'hash' => 'hash1',
                'src' => __DIR__ . '/data/upgrade/migrations/62312332434_mve_migration3.php',
            ),
            'test2.php' => array(
                'status' => 'changed',
                'hash' => 'hash2',
                'src' => __DIR__ . '/data/upgrade/migrations/62312332434_mve_migration3.php',
            ),
            'test3.php' => array(
                'status' => 'deleted',
                'hash' => 'hash3',
                'src' => __DIR__ . '/data/upgrade/migrations/62312332434_mve_migration3.php',
            )
        ));

        $package->extendScheme(array(
            'files' => array(
                'test4.php' => array(
                    'status' => 'deleted',
                    'hash' => 'hash4'
                )
            )
        ));

        $this->assertEquals(
            array(
                'migrations' => array(
                    '62312332434_mve_migration3.php',
                    '62312332434_test_migration1.php',
                    '62312332434_test_migration2.php',
                ),
                'validators' => array(
                    'validator1',
                    'validator2',
                ),
                'files' => array(
                    'test.php' => array(
                        'status' => 'new',
                        'hash' => 'hash1'
                    ),
                    'test2.php' => array(
                        'status' => 'changed',
                        'hash' => 'hash2'
                    ),
                    'test3.php' => array(
                        'status' => 'deleted',
                        'hash' => 'hash3'
                    ),
                    'test4.php' => array(
                        'status' => 'deleted',
                        'hash' => 'hash4'
                    )
                ),
                'scripts' => array(
                    'pre' => 'pre_script.php',
                    'post' => 'post_script.php',
                )
            ),
            $package->getSchema()
        );

        $this->assertFileExists(__DIR__ . '/data/runtime/upgrade/package/test.php');
        $this->assertFileExists(__DIR__ . '/data/runtime/upgrade/package/test2.php');
        $this->assertFileNotExists(__DIR__ . '/data/runtime/upgrade/package/test3.php');

        $package->setPreScriptExtraCode("/**** EXTRA_CODE ****/");
        $package->create(__DIR__ . '/data/runtime/upgrade/package.zip');

        $this->assertFileExists(__DIR__ . '/data/runtime/upgrade/package.json');
        $this->assertFileExists(__DIR__ . '/data/runtime/upgrade/package.zip');

        $code = <<<DATA
<?php
/**** EXTRA_CODE ****/


/**** THIS IS PRE SCRIPT ****/
DATA;

        $this->assertStringEqualsFile(__DIR__ . '/data/runtime/upgrade/scripts/pre_script.php', $code);
    }
}