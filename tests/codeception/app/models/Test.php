<?php

namespace app\models;

use inblank\image\ImageBehavior;
use yii\db\ActiveRecord;

/**
 * Class Test
 * @package app\models
 *
 * @property integer $id
 * @property string $name
 * @property string $image
 * @property string $imageFile
 * @property string $imageAbsolutePath
 * @method bool hasImage()
 * @method bool imageChange($image)
 * @method void imageReset()
 */
class Test extends ActiveRecord
{

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'image' => 'Image',
        ];
    }

    public function behaviors()
    {
        return [
            'image' => [
                'class' => ImageBehavior::className(),
            ]
        ];
    }
}
