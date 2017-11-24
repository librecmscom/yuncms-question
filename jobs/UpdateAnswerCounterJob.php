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
use yuncms\question\models\QuestionAnswer;

/**
 * 异步更新计数器
 * @package yuncms\article\jobs
 */
class UpdateAnswerCounterJob extends BaseObject implements RetryableJobInterface
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string 字段名
     */
    public $field;

    /**
     * @var integer
     */
    public $counter = 1;

    /**
     * @param Queue $queue
     */
    public function execute($queue)
    {
        if (($model = QuestionAnswer::findOne(['id' => $this->id])) != null) {
            $model->updateCounters([$this->field => $this->counter]);
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