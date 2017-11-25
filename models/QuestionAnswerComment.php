<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\question\models;

use Yii;
use yuncms\comment\models\Comment;
use yuncms\comment\models\CommentQuery;
use yuncms\question\jobs\UpdateAnswerCounterJob;

/**
 * Class QuestionAnswerComment
 * @package yuncms\question\models
 */
class QuestionAnswerComment extends Comment
{
    const TYPE = 'yuncms\question\models\QuestionAnswer';

    /**
     * @return void
     */
    public function init()
    {
        $this->model_class = self::TYPE;
        parent::init();
    }

    /**
     * @return CommentQuery
     */
    public static function find()
    {
        return new CommentQuery(get_called_class(), ['model_class' => self::TYPE, 'tableName' => self::tableName()]);
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        $this->model_class = self::TYPE;
        Yii::$app->queue->push(new UpdateAnswerCounterJob(['id' => $this->model_id, 'field' => 'comments', 'counters' => 1]));
        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterDelete()
    {
        Yii::$app->queue->push(new UpdateAnswerCounterJob(['id' => $this->model_id, 'field' => 'comments', 'counters' => -1]));
        parent::afterDelete();
    }
}