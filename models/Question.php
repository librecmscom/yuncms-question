<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\question\models;

use creocoder\taggable\TaggableBehavior;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Markdown;
use yii\helpers\Inflector;
use yii\helpers\HtmlPurifier;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yuncms\collection\models\Collection;
use yuncms\tag\models\Tag;
use yuncms\user\jobs\UpdateExtraCounterJob;
use yuncms\user\models\User;


/**
 * Question Model
 * @package artkost\qa\models
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $title
 * @property string $alias
 * @property Tag[] $tags
 * @property integer $price
 * @property string $content
 * @property string $body
 * @property integer $answers
 * @property integer $views
 * @property integer $votes
 * @property integer $followers
 * @property integer $collections
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at

 *
 * @property User $user
 * @property boolean hide
 */
class Question extends ActiveRecord
{
    //正常
    const STATUS_ACTIVE = 1;

    //结束
    const STATUS_END = 2;


    /**
     * Markdown processed content
     * @var string
     */
    public $body;

    /**
     * @inheritdoc
     */
    public static function find()
    {
        return new QuestionQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%question}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            [
                'class' => AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'alias'
                ],
                'value' => function ($event) {
                    return Inflector::slug($event->sender->title);
                }
            ],
            [
                'class' => AttributeBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_AFTER_FIND => 'body'
                ],
                'value' => function ($event) {
                    return HtmlPurifier::process(Markdown::process($event->sender->content, 'gfm'));
                }
            ],
            'tag' => [
                'class' => TaggableBehavior::className(),
                'tagValuesAsArray' => false,
                'tagRelation' => 'tags',
                'tagValueAttribute' => 'name',
                'tagFrequencyAttribute' => 'frequency',
            ],
            'blameable' => [
                'class' => BlameableBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'user_id',
                ],
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'content', 'tagValues'], 'required'],
            ['price', 'integer', 'min' => 0, 'max' => Yii::$app->user->identity->extra->coins, 'tooBig' => Yii::t('question', 'Insufficient points, please recharge.'),
                'tooSmall' => Yii::t('question', 'Please enter the correct points.')],
            ['hide', 'boolean'],
            ['tagValues', 'safe'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_END]],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('question', 'ID'),
            'title' => Yii::t('question', 'Title'),
            'price' => Yii::t('question', 'Reward'),
            'hide' => Yii::t('question', 'Hide'),
            'alias' => Yii::t('question', 'Alias'),
            'content' => Yii::t('question', 'Content'),
            'tagValues' => Yii::t('question', 'Tags'),
            'status' => Yii::t('question', 'Status'),
            'views' => Yii::t('question', 'Views'),
            'answers' => Yii::t('question', 'Answers'),
            'followers' => Yii::t('question', 'Followers'),
            'collections' => Yii::t('question', 'Collections'),
            'comments' => Yii::t('question', 'Comments'),
            'created_at' => Yii::t('question', 'Created At'),
            'updated_at' => Yii::t('question', 'Updated At'),
        ];
    }

    /**
     * Tag Relation
     * @return \yii\db\ActiveQueryInterface
     */
    public function getTags()
    {
        return $this->hasMany(Tag::className(), ['id' => 'tag_id'])->viaTable('{{%question_tag}}', ['question_id' => 'id']);
    }

    /**
     * Answer Relation
     * @return \yii\db\ActiveQuery
     */
    public function getAnswers()
    {
        return $this->hasMany(QuestionAnswer::className(), ['question_id' => 'id']);
    }

    /**
     * User Relation
     * @return \yii\db\ActiveQueryInterface
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * Collection Relation
     * @return \yii\db\ActiveQueryInterface
     */
    public function getCollections()
    {
        return $this->hasMany(QuestionCollection::className(), ['model_id' => 'id']);
    }

    /**
     * 是否已经收藏过
     * @param int $user_id
     * @return bool
     */
    public function isCollected($user_id)
    {
        return $this->getCollections()->andWhere(['user_id' => $user_id])->exists();
    }

    /**
     * Collection Relation
     * @return \yii\db\ActiveQueryInterface
     */
    public function getAttentions()
    {
        return $this->hasMany(QuestionAttention::className(), ['model_id' => 'id']);
    }

    /**
     * 是否已关注
     * @param int $user_id
     * @return mixed
     */
    public function isFollowed($user_id)
    {
        return $this->getAttentions()->andWhere(['user_id' => $user_id])->exists();
    }

    /**
     * 是否是作者
     * @return bool
     */
    public function isAuthor()
    {
        return $this->user_id == Yii::$app->user->id;
    }

    /**
     * 是否匿名
     * @return bool
     */
    public function isHide()
    {
        return $this->hide == 1;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            /*悬赏提问*/
            if ($this->price > 0) {
                //credit($this->user_id, 'ask', -$this->price, $this->id, $this->title);
            }
            //记录动态
            //doing($this->user_id, 'ask', get_class($this), $this->id, $this->title, mb_substr(strip_tags($this->content), 0, 200));
            /* 用户提问数+1 */
            Yii::$app->queue->push(new UpdateExtraCounterJob([
                'user_id' => $this->user_id,
                'field' => 'questions',
                'counter' => 1
            ]));
        }
    }

    /**
     * This is invoked after the record is deleted.
     */
    public function afterDelete()
    {
        $answers = QuestionAnswer::find()->where(['question_id' => $this->id])->all();
        foreach ($answers as $answer) {
            $answer->delete();
        }
        Yii::$app->queue->push(new UpdateExtraCounterJob([
            'user_id' => $this->user_id,
            'field' => 'questions',
            'counter' => -1
        ]));
        parent::afterDelete();
    }
}
