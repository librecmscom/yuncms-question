<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
/* @var $this yii\web\View */
/**
 * @var \yuncms\question\models\QuestionComment $model
 */
?>
<div class="media-left">
    <a href="<?= Url::to(['/user/space/view', 'id' => $model->user_id]) ?>" target="_blank">
        <img class="media-object avatar-27" alt="<?= Html::encode($model->user->nickname) ?>"
             src="<?= $model->user->getAvatar() ?>">
    </a>
</div>
<div class="media-body">
    <div class="media-heading">
        <a href="<?= Url::to(['/user/space/view', 'id' => $model->user_id]) ?>"
           target="_blank"><?= $model->user->nickname ?></a>
        <?php if ($model->to_user_id): ?>
            <span class="text-muted"><?=Yii::t('question','reply')?> </span>
            <a href="<?= Url::to(['/user/space/view', 'id' => $model->to_user_id]) ?>"
               target="_blank"><?= $model->toUser->nickname ?></a>
        <?php endif; ?>
    </div>
    <div class="content"><p><?= HtmlPurifier::process($model->content) ?></p></div>
    <div class="media-footer">
        <span class="text-muted"><?= Yii::$app->formatter->asRelativeTime($model->created_at); ?></span>
        <?php if (!Yii::$app->user->isGuest && $model->user_id != Yii::$app->user->id): ?>
            <a href="#" class="ml-10 comment-reply"
               data-model_id="<?= $model->model_id ?>" data-to_user_id="<?= $model->user_id ?>"
               data-model_class="<?= $model->model_class ?>"
               data-message="<?=Yii::t('question','reply')?> <?= Html::encode($model->user->nickname) ?>"><i class="fa fa-reply"></i> <?=Yii::t('question','reply')?></a>
        <?php endif; ?>
    </div>

</div>