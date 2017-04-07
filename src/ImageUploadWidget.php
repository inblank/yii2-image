<?php
/**
 * Form widget for image upload in ImageBehavior
 *
 * @link https://github.com/inblank/yii2-image
 * @copyright Copyright (c) 2017 Pavel Aleksandrov <inblank@yandex.ru>
 * @license http://opensource.org/licenses/MIT
 */

namespace inblank\image;

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\InputWidget;

/**
 * Class ImageUploadWidget
 * @property
 */
class ImageUploadWidget extends InputWidget
{
    public $registerAssets = true;

    public $messages = [
        'change' => 'Change Image',
        'clear' => 'Clear Image',
        'reset' => 'Reset Image',
    ];

    /**
     * @return string
     */
    public function run()
    {
        if ($this->registerAssets) {
            ImageAsset::register($this->getView());
        }
        $form = end(ActiveForm::$stack);
        if (!isset($form->options['enctype'])) {
            $form->options['enctype'] = 'multipart/form-data';
        }
        $str = '<div class="upload-image-preview" style="background-image: url(' . $this->model->imageUrl . ')"></div>' .

            '<div class="upload-image-control">' .

            '<a class="btn btn-primary upload-image-change" title="' . $this->messages['change'] . '">' .
            Html::activeFileInput($this->model, $this->attribute, $this->options) .
            '<span class="glyphicon glyphicon-save"></span>' .
            '</a>' .

            '<a class="btn btn-danger upload-image-clear" title="' . $this->messages['clear'] . '">' .
            '<span class="glyphicon glyphicon-trash"></span>' .
            '</a>' .

            '<a class="btn btn-success upload-image-reset" title="' . $this->messages['reset'] . '">' .
            '<span class="glyphicon glyphicon-repeat"></span>' .
            '</a>' .

            '</div>';

        $options = [
            'class' => ['upload-image'],
            'data' => ['image-empty' => $this->model->imageDefaultUrl],
        ];
        if ($this->model->imageResizeStrategy === ImageBehavior::CROP) {
            $options['class'][] = 'upload-image-crop';
        } else {
            $options['class'][] = 'upload-image-frame';
        }
        if (!$this->model->{$this->attribute}) {
            $options['class'][] = 'upload-image-empty';
        } else {
            $options['data']['image'] = $this->model->imageUrl;
        }
        return Html::tag('div', $str, $options);
    }
}
