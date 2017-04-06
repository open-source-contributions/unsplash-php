<?php

namespace Crew\Unsplash\Tests;

use \Crew\Unsplash as Unsplash;
use GuzzleHttp\Tests\Server;
use GuzzleHttp\Psr7\Response;
use \VCR\VCR;

class PhotoTest extends BaseTest
{
    public function setUp()
    {
        parent::setUp();

        $connection = new Unsplash\Connection($this->provider, $this->accessToken);
        Unsplash\HttpClient::$connection = $connection;
    }

    public function testFindPhoto()
    {
        VCR::insertCassette('photos.yml');

        $photo = Unsplash\Photo::find('ZUaqqMxtxYk');

        VCR::eject();

        $this->assertEquals('ZUaqqMxtxYk', $photo->id);
    }

    public function testFindAllPhotos()
    {
        VCR::insertCassette('photos.yml');

        $photos = Unsplash\Photo::all();

        VCR::eject();

        $this->assertEquals(10, $photos->count());
    }


    public function testFindCuratedPhotos()
    {
        VCR::insertCassette('photos.yml');

        $photos = Unsplash\Photo::curated(1, 10, 'popular');

        VCR::eject();

        $this->assertEquals(10, $photos->count());
    }

    public function testSearchPhotos()
    {
        VCR::insertCassette('photos.yml');

        $photos = Unsplash\Photo::search('coffee');

        VCR::eject();

        $this->assertEquals(10, $photos->count());
    }

    public function testPhotographer()
    {
        VCR::insertCassette('photos.yml');

        $photo = Unsplash\Photo::find('ZUaqqMxtxYk');
        $photographer = $photo->photographer();

        VCR::eject();

        $this->assertEquals($photo->user['username'], $photographer->username);
    }

    public function testPostPhotos()
    {
        VCR::insertCassette('photos.yml');

        $photo = Unsplash\Photo::create(__dir__.'/images/test.jpg');

        VCR::eject();

        $this->assertInstanceOf('Crew\Unsplash\Photo', $photo);
        $this->assertNotNull($photo->id);
    }

    /**
     * @expectedException Crew\Unsplash\Exception
     */
    public function testPostBadPathPhoto()
    {
        $photo = Unsplash\Photo::create(__dir__.'/images/bad.jpg');
    }

    public function testRandomPhoto()
    {
        VCR::insertCassette('photos.yml');

        $photo = Unsplash\Photo::random();

        VCR::eject();

        $this->assertEquals('ZUaqqMxtxYk', $photo->id);
    }

    public function testRandomPhotoWithFilters()
    {
        VCR::insertCassette('photos.yml');

        $filters = [
            'category' => [2,3],
            'featured' => true,
            'username' => 'andy_brunner',
            'query'    => 'ice',
            'w'        => 100,
            'h'        => 100
        ];

        $photo = Unsplash\Photo::random($filters);

        VCR::eject();

        $this->assertEquals('ZUaqqMxtxYk', $photo->id);
        $this->assertEquals('https://unsplash.imgix.net/photo-1428681756973-d318f055795a?q=75&fm=jpg&w=100&h=100&fit=max&s=b223d24e28ba1ced6731e98d46cd5f83', $photo->urls['custom']);
    }

    public function testLikePhoto()
    {
        VCR::insertCassette('photos.yml');

        $photo = Unsplash\Photo::find('Fma1wE_zIf8');
        $like = $photo->like();

        VCR::eject();

        $this->assertTrue($like);
    }

    public function testUnlikePhoto()
    {
        VCR::insertCassette('photos.yml');

        $photo = Unsplash\Photo::find('j0g8taxHZa0');
        $like = $photo->like();

        $unlike = $photo->unlike();

        VCR::eject();

        $this->assertTrue($unlike);
    }

    public function testStatisticsForPhoto()
    {
        VCR::insertCassette('photos.yml');
        $photo = Unsplash\Photo::find('ZUaqqMxtxYk');
        $response = $photo->statistics();
        $this->assertInstanceOf('ArrayObject', $response);
        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('downloads', $response);
        $this->assertArrayHasKey('views', $response);
        $this->assertArrayHasKey('likes', $response);
        VCR::eject();
    }

    public function testDownloadLinkForPhoto()
    {
        VCR::insertCassette('photos.yml');
        $photo = Unsplash\Photo::find('ZUaqqMxtxYk');
        $link = $photo->download();
        $this->assertInternalType('string', $link);
        $this->assertNotFalse(filter_var($link, FILTER_VALIDATE_URL));
        VCR::eject();
    }

    public function testUpdatePhoto()
    {
        VCR::insertCassette('photos.yml');
        $photo = Unsplash\Photo::find('GQcfdBoVB_g');
        $photo->update(['exif' => ['focal_length' => 10]]);
        $this->assertEquals(10, $photo->exif['focal_length']);
        VCR::eject();
    }

    public function testAllPhotosOrderedLatest()
    {
        VCR::insertCassette('photos.yml');

        $photos = Unsplash\Photo::all(1, 10, 'latest');
        $this->assertEquals(10, $photos->count());
        VCR::eject();
    }
}