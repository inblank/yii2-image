<?php
/**
 * Image behavior for ActiveRecord
 *
 * @link https://github.com/inblank/yii2-image
 * @copyright Copyright (c) 2016 Pavel Aleksandrov <inblank@yandex.ru>
 * @license http://opensource.org/licenses/MIT
 */
namespace inblank\image;

use Imagine\Filter\Advanced\Canvas;
use Imagine\Filter\Transformation;
use Imagine\Image\Box;
use Imagine\Image\Color;
use Imagine\Image\ImageInterface;
use Imagine\Image\Point;
use yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;

/**
 * Class Image
 *
 * @property ActiveRecord $owner
 */
class ImageBehavior extends Behavior
{
    /** Just proportional resize image to fit size */
    const NONE = 0;
    /** Crop image resize strategy */
    const CROP = 1;
    /** Add frame image resize strategy */
    const FRAME = 2;
    /**
     * Name of attribute to store the image
     * @var string
     */
    public $imageAttribute = "image";
    /**
     * Default image name
     * @var string
     */
    public $imageDefault = "image.png";
    /**
     * Path to store image files.
     * If empty will be init to /images/<ActiveRecord Class Name>
     * @var string
     */
    public $imagePath;
    /**
     * Size to convert image
     * If array: [width, height].
     * If integer: use value for with and height.
     * If not set: image save as is.
     * @var integer|array
     */
    public $imageSize;
    /**
     * Image resize strategy
     * @var int
     */
    public $imageResizeStrategy = self::NONE;
    /**
     * Image frame color
     * @var
     */
    public $imageFrameColor = "#FFF";
    /**
     * Calculated absolute image path relative to webroot
     * @var
     */
    protected $_imageAbsolutePath;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
            ActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
        ];
    }

    /**
     * After save action
     */
    public function afterSave()
    {
        $this->imageChangeByUpload();
    }

    /**
     * After delete action
     */
    public function afterDelete()
    {
        $this->imageRemoveFile();
    }

    /**
     * Get image path
     * @return string
     */
    public function getImagePath()
    {
        if ($this->imagePath === null) {
            $this->imagePath = '/images/' . strtolower((new \ReflectionClass($this->owner))->getShortName());
        }
        return $this->imagePath;
    }

    /**
     * Get default image URL
     * @return string
     * @throws yii\base\InvalidConfigException
     */
    public function getImageDefaultUrl()
    {
        return $this->_imageUrl($this->imageDefault);
    }

    /**
     * Get absolute team image path in filesystem
     * @return string
     */
    public function getImageAbsolutePath()
    {
        if ($this->_imageAbsolutePath === null) {
            $this->_imageAbsolutePath = Yii::getAlias('@webroot') .
                '/' . (defined('IS_BACKEND') ? '../' : '') .
                ltrim($this->getImagePath(), '/');
            if (!file_exists($this->_imageAbsolutePath)) {
                yii\helpers\FileHelper::createDirectory($this->_imageAbsolutePath);
            }
        }
        return $this->_imageAbsolutePath;
    }

    /**
     * Check team image
     * @return bool
     * @throws yii\base\InvalidConfigException
     */
    public function hasImage()
    {
        $image = $this->owner->getAttribute($this->imageAttribute);
        return !empty($image) && $image != $this->imageDefault;
    }

    /**
     * Check that image file exists
     */
    public function imageFileExists()
    {
        return file_exists($this->getImageFile());
    }

    /**
     * Get team image URL
     * @return string
     * @throws yii\base\InvalidConfigException
     */
    public function getImageUrl()
    {
        return !$this->hasImage() ?
            $this->getImageDefaultUrl() :
            $this->_imageUrl($this->owner->getAttribute($this->imageAttribute));
    }

    /**
     * Return filename in filesystem
     * @return string
     */
    public function getImageFile()
    {
        return $this->getImageAbsolutePath() . '/' . $this->owner->getAttribute($this->imageAttribute);
    }

    /**
     * Change image by uploaded file
     */
    public function imageChangeByUpload()
    {
        $formName = $this->owner->formName();
        if (!empty($_FILES[$formName]['tmp_name'][$this->imageAttribute])) {
            $this->imageChange(
                [$_FILES[$formName]['name'][$this->imageAttribute] => $_FILES[$formName]['tmp_name'][$this->imageAttribute]]
            );
        }
    }

    /**
     * Change image
     * @param string|array $sourceFile source file. if set as array ['fileName' => 'file_in_filesystem']
     * @return bool true, if image was changed and old image file was deleted. false, if image not changed
     * @throws yii\base\InvalidConfigException
     */
    public function imageChange($sourceFile)
    {
        if (is_array($sourceFile)) {
            $fileName = key($sourceFile);
            $sourceFile = current($sourceFile);
        } else {
            $fileName = $sourceFile;
        }
        if (!file_exists($sourceFile)) {
            return false;
        }
        $imageName = $this->imageAttribute . '_' .
            md5(implode('-', (array)$this->owner->getPrimaryKey()) . microtime(true) . rand()) .
            '.' . pathinfo($fileName)['extension'];
        $destinationFile = $this->getImageAbsolutePath() . '/' . $imageName;
        if (!copy($sourceFile, $destinationFile)) {
            return false;
        }
        $this->imageRemoveFile();
        if (!empty($this->imageSize)) {
            $size = $this->imageSize;
            if (!is_array($size)) {
                $size = [$size, $size];
            }
            $newBox = new Box($size[0], $size[1]);
            $image = (new Transformation())->thumbnail(
                $newBox,
                $this->imageResizeStrategy == self::CROP ?
                    ImageInterface::THUMBNAIL_OUTBOUND : ImageInterface::THUMBNAIL_INSET
            )->apply(yii\imagine\Image::getImagine()->open($destinationFile));
            $currentBox = $image->getSize();
            if ($this->imageResizeStrategy === self::FRAME) {
                $image = (new Transformation())->add(
                    new Canvas(
                        yii\imagine\Image::getImagine(),
                        $newBox,
                        $size[0] == $currentBox->getWidth() ?
                            new Point(0, ($size[1] - $currentBox->getHeight()) / 2) :
                            new Point(($size[0] - $currentBox->getWidth()) / 2, 0),
                        new Color($this->imageFrameColor)
                    )
                )->apply($image);
            }
            $image->save($destinationFile);
        }
        $this->owner->setAttribute($this->imageAttribute, $imageName);
        $this->owner->updateAttributes([$this->imageAttribute]);
        return true;
    }

    /**
     * Reset image to default
     * @throws yii\base\InvalidConfigException
     */
    public function imageReset()
    {
        $this->imageRemoveFile();
        $this->owner->setAttribute($this->imageAttribute, null);
        $this->owner->updateAttributes([$this->imageAttribute]);
    }

    /**
     * Remove current image
     * @throws yii\base\InvalidConfigException
     */
    protected function imageRemoveFile()
    {
        if ($this->hasImage()) {
            @unlink($this->getImageFile());
        }
    }

    /**
     * Make image URL
     * @param string $imageFileName image filename
     * @return string
     * @throws yii\base\InvalidConfigException
     */
    protected function _imageUrl($imageFileName)
    {
        return $this->getImagePath() . '/' . $imageFileName;
    }

}
