<?php

/**
 * Class CoreReleaseArchiveTest
 */
class AddonReleaseArchiveTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider releaseDataProvider
     */
    public function testCoreRelease($file, $version, $name)
    {
        $release = new \upgrade\builder\AddonReleaseArchive($file);

        $this->assertEquals($version, $release->getVersion());
        $this->assertEquals($name, $release->getName());
    }

    public function releaseDataProvider()
    {
        return array(
            array(__DIR__ . '/data/releases/addon_v1.0.0.tgz', '1.0.0', 'sample'),
            array(__DIR__ . '/data/releases/addon_v2.0.0.tgz', '2.0.0', 'sample')
        );
    }
}