<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\question\models;

use Yii;
use yuncms\collection\models\Collection;
use yuncms\collection\models\CollectionQuery;
use yuncms\question\jobs\UpdateAnswerCounterJob;

/**
 * Class Support
 * @property string $subject
 * @package yuncms\article\models
 */
class QuestionCollection extends Collection
{
    const TYPE = 'yuncms\question\models\Question';

    /**
     * @return void
     */
    public function init()
    {
        $this->model_class = self::TYPE;
        parent::init();
    }

    /**
     * @return CollectionQuery
     */
    public static function find()
    {
        return new CollectionQuery(get_called_class(), ['model_class' => self::TYPE, 'tableName' => self::tableName()]);
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        $this->model_class = self::TYPE;
        Yii::$app->queue->push(new UpdateAnswerCounterJob(['id' => $this->model_id, 'field' => 'collections', 'counters' => 1]));
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        Yii::$app->queue->push(new UpdateAnswerCounterJob(['id' => $this->model_id, 'field' => 'collections', 'counters' => -1]));
        parent::afterDelete();
    }
}