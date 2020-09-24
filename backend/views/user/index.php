<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Users';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">


    <p>
        <?php if(Yii::$app->user->can('user/create')) : ?>
            <?= Html::a('Create User', ['create'], ['class' => 'btn btn-success']) ?>
        <?php endif; ?>

    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'username',
            'last_name',
            'first_name',
            'birth_date',
            'last_login',
            [

                'attribute' => 'role',
                'label'=>'Role',
                'value' => function($data) {
                    return $data->getCurrentRoleTitle();
                }
            ],
            [

                'attribute' => 'status',
                'label'=>'Status',
                'value' => function($data) {
                    if($data->status == \common\models\User::STATUS_ACTIVE){
                        return 'Active';
                    } else {
                        return 'Inactive';
                    }
                }
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'header'=>Yii::t('app', 'Actions'),
                'template' => '{view} {update} {delete}',
                'visibleButtons' => [
                    'view' => true,
                    'update' => Yii::$app->user->can('user/update'),
                    'delete' => Yii::$app->user->can('user/delete'),
                ],



            ],
        ],
    ]); ?>

</div>
