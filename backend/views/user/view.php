<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use \common\models\User;

/* @var $this yii\web\View */
/* @var $model common\models\User */

$this->title = $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="user-view">



    <p>
        <?php if(Yii::$app->user->can('user/update')) : ?>
            <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?php endif; ?>
        <?php if($model->status == User::STATUS_ACTIVE && $model->hasOtherActives() && Yii::$app->user->can('user/make-inactive')) : ?>
            <?= Html::a('Make Inactive', ['make-inactive', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
        <?php endif; ?>
        <?php if($model->status == User::STATUS_INACTIVE && Yii::$app->user->can('user/make-active')) : ?>
            <?= Html::a('Make Active', ['make-active', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?php endif; ?>
        <?php if(Yii::$app->user->can('user/delete')) : ?>
            <?= Html::a('Delete', ['delete', 'id' => $model->id], [
                'class' => 'btn btn-danger',
                'data' => [
                    'confirm' => 'Are you sure you want to delete this item?',
                    'method' => 'post',
                ],
            ]) ?>
        <?php endif; ?>

    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'username',
            'last_name',
            'first_name',
            'birth_date',
            'comment:ntext',
            'last_login',
            'email:email',
            'status',
            'created_at',
            'updated_at',
            [
                'label'=>'role',
                'value' => function (User $model) {
                    return $model->getCurrentRoleTitle();
                },
            ],
            [

                'attribute'=>'Avatart',

                'value'=>'/'.$model->avatar,

                'format' => ['image',['width'=>'200','height'=>'200']],

            ]
        ],
    ]) ?>

</div>
