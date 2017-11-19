<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */
namespace yuncms\question\models;

use yii\db\ActiveQuery;
use creocoder\taggable\TaggableQueryBehavior;

/**
 * Class QuestionQuery
 * @method ActiveQuery anyTagValues($values, $attribute = null)
 * @method ActiveQuery allTagValues($values, $attribute = null)
 * @method ActiveQuery relatedByTagValues($values, $attribute = null)
 * @package yuncms\question\models
 */
class QuestionQuery extends ActiveQuery
{

    public function behaviors()
    {
        return [
            TaggableQueryBehavior::className(),
        ];
    }

    /**
     * Apply possible questions order to query
     * @param string $order
     * @return void
     */
    public function applyOrder($order)
    {
        if ($order == 'new') {//按发布时间倒序
            $this->newest();
        } elseif ($order == 'hottest') {//热门问题
            $this->hottest();
        } elseif ($order == 'reward') {//悬赏问题
            $this->reward();
        } elseif ($order == 'unanswered') {//未回答问题
            $this->unAnswered();
        }
    }

    /**
     * 查询活动的代码
     * @return $this
     */
    public function active()
    {
        return $this->andWhere(['>','status' ,0]);
    }

    /**
     * 热门问题
     */
    public function hottest()
    {
        return $this->active()->orderBy(['(answers / pow((((UNIX_TIMESTAMP(NOW()) - created_at) / 3600) + 2),1.8) )' => SORT_DESC]);
    }

    /**
     * 最新问题
     * @return $this
     */
    public function newest()
    {
        return $this->active()->orderBy(['created_at' => SORT_DESC]);
    }

    /**
     * 未回答的
     * @return $this
     */
    public function unAnswered()
    {
        return $this->active()->andWhere(['answers' => 0])->orderBy(['created_at' => SORT_DESC]);
    }

    /**
     * 悬赏问题
     * @return $this
     */
    public function reward()
    {
        return $this->active()->andWhere(['>', 'price', 0])->orderBy(['created_at' => SORT_DESC]);
    }

    /**
     * @param $limit
     * @return static
     */
    public function views($limit)
    {
        return $this->active()->andWhere(['>', 'views', $limit]);
    }
}