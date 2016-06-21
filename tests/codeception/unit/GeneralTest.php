<?php

namespace inblank\image\tests;

use app\models\Test;
use Codeception\Specify;
use tests\codeception\_fixtures\TestFixture;
use yii;
use yii\codeception\TestCase;

class GeneralTest extends TestCase
{
    use Specify;

    public function fixtures()
    {
        return [
            'test' => TestFixture::className(),
        ];
    }

    /**
     * General test
     */
    public function testGeneral()
    {
        $this->specify("we want to change model image", function () {
            $imageFile = __DIR__ . '/../_data/test-image.png';
            $imageFile2 = __DIR__ . '/../_data/test-image2.jpg';
            $defaultImageFile = (new Test())->imageAbsolutePath . '/image.png';

            /** @var Test $test */
            $test = $this->getFixture('test')->getModel('empty');
            expect("the model does not have image", $test->hasImage())->false();
            expect("we can change image on existing file", $test->imageChange($imageFile))->true();
            expect('model must have the image', $test->hasImage())->true();
            expect_file('we can see new image', $test->imageFile)->exists();
            expect_file("default image cannot be removed", $defaultImageFile)->exists();
            $oldImage = $test->image;
            $oldImageFile = $test->imageFile;

            expect("we can change image again", $test->imageChange($imageFile2))->true();
            expect('image must be changed', $test->image)->notEquals($oldImage);
            expect_file('we can see new image', $test->imageFile)->exists();
            expect_file("old image must be removed", $oldImageFile)->notExists();
            expect_file("default image cannot be removed", $defaultImageFile)->exists();

            $oldImageFile = $test->imageFile;
            expect("we can't change logo on not existing file", $test->imageChange('123'))->false();

            /** @var Test $test2 */
            $test2 = $this->getFixture('test')->getModel('empty2');
            expect("we must change image for other model", $test2->imageChange($imageFile))->true();
            expect_file('we can see image', $test2->imageFile)->exists();
            expect_file('we can see image for first model', $test->imageFile)->exists();
            expect_file("we can see default image", $defaultImageFile)->exists();

            $test->imageReset();
            expect('image attribute must be null', $test->image)->null();
            expect_file('we cannot see image file', $oldImageFile)->notExists();
            expect_file("default image cannot be removed", $defaultImageFile)->exists();
        });

    }

    /**
     * General test
     */
    public function testCreateAndDelete()
    {
        $this->specify("we want to change model image", function () {
            $imageFile = __DIR__ . '/../_data/test-image.png';

            /** @var Test $test */
            $test = new Test();
            $test->name = 'Simple test';

            expect("model must be saved", $test->save())->true();
            expect("the model does not have image", $test->hasImage())->false();
            expect("we can change image on existing file", $test->imageChange($imageFile))->true();
            expect('model must have the image', $test->hasImage())->true();
            expect_file('image file must be exist', $test->imageFile)->exists();

            $imageFile = $test->imageFile;
            $test->delete();
            expect_file('image file must not be exist', $imageFile)->notExists();
        });

    }

    /**
     * General test
     */
    public function testResize()
    {
        $this->specify("we want to change model image", function () {
            $imageFile = __DIR__ . '/../_data/test-image.png';

            /** @var Test $test */
            $test = new Test();
            $test->name = 'Simple test';
            expect("model must be saved", $test->save())->true();
            $test->imageSize = 50;
            expect("we can change image on existing file", $test->imageChange($imageFile))->true();
            $size = getimagesize($test->imageFile);
            expect("image width", $test->imageSize)->equals($size[0]);
            expect("image height", $test->imageSize)->equals($size[1]);
        });

    }

    protected function setUp()
    {
        parent::setUp();
        // set web server root alias for yii
        Yii::setAlias('@webroot', realpath(__DIR__ . '/../app/web'));
        // empty images dir
        yii\helpers\FileHelper::removeDirectory(__DIR__ . '/../app/web/images/test');
        yii\helpers\FileHelper::createDirectory(__DIR__ . '/../app/web/images/test');
        // copy default image
        copy(__DIR__ . '/../_data/image.png', __DIR__ . '/../app/web/images/test/image.png');
    }

    protected function tearDown()
    {
        parent::tearDown();
        // remove images dir
        yii\helpers\FileHelper::removeDirectory(__DIR__ . '/../app/web/images/test');
    }
}
