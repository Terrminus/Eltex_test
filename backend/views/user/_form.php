<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $model common\models\User */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-6">
            <div class="box box-info">
                <div class="box-body">
                    <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'password')->passwordInput() ?>

                    <?= $form->field($model, 'last_name')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'first_name')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'birth_date')->widget(DatePicker::classname(), [
                        'options' => ['placeholder' => 'Enter birth date ...'],
                        'pluginOptions' => [
                            'format' => 'yyyy-mm-dd',
                            'autoclose'=>true
                        ]
                    ]) ?>

                    <?= $form->field($model, 'comment')->textarea(['rows' => 6]) ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="box box-info">
                <div class="box-body">
                    <?= $form->field($model, 'status')->dropDownList([
                        \common\models\User::STATUS_ACTIVE => 'Active',
                        \common\models\User::STATUS_INACTIVE => 'Inactive'
                    ]) ?>

                    <?= $form->field($model, 'email')->input('email') ?>

                    <?= $form->field($model, 'role')->dropDownList($roles_list) ?>

                    <?= $form->field($model, 'avatar_file')->fileInput() ?>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
