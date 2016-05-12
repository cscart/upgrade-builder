<?php

/**
 * Class CoreReleaseArchiveTest
 */
class CoreReleaseArchiveTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider releaseDataProvider
     */
    public function testCoreRelease($file, $version, $name)
    {
        $release = new \upgrade\builder\CoreReleaseArchive($file);

        $this->assertEquals($version, $release->getVersion());
        $this->assertEquals($name, $release->getName());
    }

    public function releaseDataProvider()
    {
        return array(
            array(__DIR__ . '/data/releases/ultimate_4.3.4.tgz', '4.3.4', 'ultimate'),
            array(__DIR__ . '/data/releases/ultimate_4.3.5.tgz', '4.3.5', 'ultimate'),
            array(__DIR__ . '/data/releases/mve_4.3.4.tgz', '4.3.4', 'mve'),
        );
    }
}