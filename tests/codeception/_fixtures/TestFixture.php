<?php
namespace tests\codeception\_fixtures;

use yii\test\ActiveFixture;

class TestFixture extends ActiveFixture{
    public $modelClass = 'app\models\Test';
    public $dataFile = '@tests/codeception/_fixtures/data/test.php';
}
