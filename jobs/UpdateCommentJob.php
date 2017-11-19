<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */
namespace yuncms\question\jobs;

use yii\base\BaseObject;
use yii\queue\Queue;
use yii\queue\RetryableJobInterface;
use yuncms\question\models\Question;

/**
 * Class UpdateCommentJob
 * @package yuncms\question\jobs
 */
class UpdateCommentJob extends BaseObject implements RetryableJobInterface
{
    public $id;

    /**
     * @param Queue $queue
     */
    public function execute($queue)
    {
        if (($model = Question::findOne(['id' => $this->id])) != null) {
            $model->updateCounters(['comments' => 1]);
        }
    }

    /**
     * @inheritdoc
     */
    public function getTtr()
    {
        return 60;
    }

    /**
     * @inheritdoc
     */
    public function canRetry($attempt, $error)
    {
        return $attempt < 3;
    }
}