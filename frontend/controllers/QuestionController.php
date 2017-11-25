<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\question\frontend\controllers;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Url;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;
use yuncms\question\models\Question;
use yuncms\question\models\QuestionAnswer;
use yuncms\question\models\QuestionAttention;
use yuncms\question\models\QuestionCollection;
use yuncms\tag\models\Tag;

/**
 * Class QuestionController
 *
 * @package yuncms\question
 */
class QuestionController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index', 'view', 'tag', 'auto-complete'],
                        'roles' => ['@', '?']
                    ],
                    [
                        'allow' => true,
                        'actions' => ['upload', 'create',
                            'append-reward', 'update',
                            'delete',
                            'collection', 'attention'
                        ],
                        'roles' => ['@']
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'upload' => [
                'class' => 'yuncms\summernote\SummerNoteAction',
                //etc...
            ],
        ];
    }

    /**
     * 问题首页
     *
     * @return string
     */
    public function actionIndex()
    {
        $query = Question::find()->with('user');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $query->applyOrder(Yii::$app->request->get('order', 'new'));
        return $this->render('index', ['dataProvider' => $dataProvider]);
    }

    /**
     * Tag 下拉加载
     * @param string $query
     * @return array
     * @throws InvalidConfigException
     */
    public function actionAutoComplete($query)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (mb_strlen($query) < 2) {
            if (YII_DEBUG) {
                throw new InvalidConfigException ("Operator 'query' requires two operands.");
            } else {
                return [];
            }
        } else {
            $rows = Tag::find()->select(['name as id', 'name', 'name as text', 'frequency'])
                ->where(['like', 'name', $query])
                ->orderBy(['frequency' => SORT_DESC])
                ->limit(20)
                ->asArray()
                ->all();
            return $rows;
        }
    }

    /**
     * 显示标签页
     *
     * @param string $tag 标签
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionTag($tag)
    {
        Url::remember('', 'actions-redirect');
        if (($model = Tag::findOne(['name' => $tag])) != null) {
            $query = Question::find()->anyTagValues($tag, 'name')->with('user');
            $query->andWhere(['>', Question::tableName() . '.status', 0]);
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
            ]);
            return $this->render('tag', ['model' => $model, 'dataProvider' => $dataProvider]);
        } else {
            throw new NotFoundHttpException (Yii::t('yii', 'The requested page does not exist.'));
        }
    }

    /**
     * 提问
     *
     * @return \yii\web\Response|string
     */
    public function actionCreate()
    {
        $model = new Question();
        if ($model->load(Yii::$app->request->post()) && $model->save() != null) {
            Yii::$app->session->setFlash('success', 'question Submitted');
            return $this->redirect(['view', 'id' => $model->id]);
        }
        return $this->render('create', ['model' => $model]);
    }

    /**
     * 修改问题
     *
     * @param integer $id
     * @return \yii\web\Response|string
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        /** @var Question $model */
        $model = $this->findModel($id);
        if ($model->isAuthor()) {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                Yii::$app->session->setFlash('success', 'question Submitted');
                return $this->redirect(['view', 'id' => $model->id]);
            }
            return $this->render('update', ['model' => $model]);
        } else {
            Yii::$app->session->setFlash('danger', Yii::t('yii', 'You are not allowed to perform this action.'));
            return $this->redirect(['/question/question/view', 'id' => $model->id]);
        }
    }

    /**
     * 追加悬赏
     * @param int $id
     * @return \yii\web\Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionAppendReward($id)
    {
        /** @var Question $model */
        $model = $this->findModel($id);
        if ($model->isAuthor()) {
            $coins = Yii::$app->request->post('coins', 0);
            //此处开始扣钱
            $transaction = Question::getDb()->beginTransaction();
            try {
                if (function_exists('credit')) {
                    credit($model->user_id, 'answer_adopted', -$coins, $model->id, $model->title);
                }

                $model->updateCounters(['price' => $coins]);
                $model->save();
                if (function_exists('doing')) {
                    doing($model->user_id, 'append_reward', get_class($model), $model->id, $model->title, "追加了 " . $coins . " 个积分");
                }
                Yii::$app->session->setFlash('success', 'question Submitted');
                $transaction->commit();
            } catch (\Exception $e) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'question Submitted');
            }
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            Yii::$app->session->setFlash('danger', Yii::t('yii', 'You are not allowed to perform this action.'));
            return $this->redirect(['/question/question/view', 'id' => $model->id]);
        }
    }

    /**
     * 查看问题
     *
     * @param integer $id
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        /** @var Question $model */
        $model = $this->findModel($id);

        /*问题查看数+1*/
        if (!$model->isAuthor()) $model->updateCounters(['views' => 1]);

        /*已解决问题*/
        $bestAnswer = null;
        if ($model->status === Question::STATUS_END) {
            $bestAnswer = $model->getAnswers()->where(['>', 'adopted_at', '0'])->one();
        }
        /** @var QuestionAnswer $query 回答列表 */
        $query = $model->getAnswers()->with('user');

        $answerOrder = $query->applyOrder(Yii::$app->request->get('answers', 'supports'));

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $this->render('view', [
            'model' => $model,
            'answerOrder' => $answerOrder,
            'bestAnswer' => $bestAnswer,
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * 删除问题
     *
     * @param integer $id
     * @return \yii\web\Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->isAuthor() && $model->delete()) {
            return $this->redirect(['index']);
        } else {
            Yii::$app->session->setFlash('danger', Yii::t('yii', 'You are not allowed to perform this action.'));
            return $this->redirect(['/question/question/view', 'id' => $model->id]);
        }
    }

    /**
     * 收藏问题
     * @return array
     * @throws ServerErrorHttpException
     */
    public function actionCollection()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $source = $this->findModel(Yii::$app->request->post('model_id'));
        if (($collect = QuestionCollection::find()->where(['model_id' => $source->id, 'user_id' => Yii::$app->user->getId()])->one()) != null) {
            $collect->delete();
            return ['status' => 'unCollect'];
        } else {
            $model = new QuestionCollection();
            $model->subject = $source->title;
            $model->model_id = $source->id;
            $model->load(Yii::$app->request->post(), '');
            if ($model->save() === false && !$model->hasErrors()) {
                throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
            }
            return ['status' => 'collected'];
        }
    }

    /**
     * 关注问题
     * @return array
     * @throws ServerErrorHttpException
     */
    public function actionAttention()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $source = $this->findModel(Yii::$app->request->post('model_id'));
        if (($attention = $source->getAttentions()->andWhere(['user_id' => Yii::$app->user->getId()])->one()) != null) {
            $attention->delete();
            return ['status' => 'unfollowed'];
        } else {
            $model = new QuestionAttention();
            $model->load(Yii::$app->request->post(), '');
            $model->model_id = $source->id;
            //$model->user_id = Yii::$app->user->getId();
            if ($model->save() === false && !$model->hasErrors()) {
                throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
            }
            return ['status' => 'followed'];
        }
    }

    /**
     * 获取模型
     *
     * @param integer $id
     * @return Question
     * @throws NotFoundHttpException
     */
    public function findModel($id)
    {
        if (($model = Question::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException (Yii::t('yii', 'The requested page does not exist.'));
        }
    }
}