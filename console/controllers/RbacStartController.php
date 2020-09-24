<?php
namespace console\controllers;

use common\models\User;
use Yii;
use yii\console\Controller;

class RbacStartController extends Controller
{
    public function actionInit()
    {
        $auth = Yii::$app->authManager;

        $auth->removeAll();

        $user_make_active_permission = $auth->createPermission('user/make-active');
        $auth->add($user_make_active_permission);
        $user_make_inactive_permission = $auth->createPermission('user/make-inactive');
        $auth->add($user_make_inactive_permission);
        $user_create = $auth->createPermission('user/create');
        $auth->add($user_create);
        $user_update = $auth->createPermission('user/update');
        $auth->add($user_update);
        $user_delete = $auth->createPermission('user/delete');
        $auth->add($user_delete);

        // adding "moderator" role
        $moderator_role = $auth->createRole('moderator');
        $auth->add($moderator_role);
        $auth->addChild($moderator_role,$user_make_active_permission);
        $auth->addChild($moderator_role,$user_make_inactive_permission);

        // adding "creator" role
        $creator_role = $auth->createRole('creator');
        $auth->add($creator_role);
        $auth->addChild($creator_role,$user_create);


        // adding "admin" role

        $admin_role = $auth->createRole('admin');
        $auth->add($admin_role);
        $auth->addChild($admin_role,$user_make_active_permission);
        $auth->addChild($admin_role,$user_make_inactive_permission);
        $auth->addChild($admin_role,$user_create);
        $auth->addChild($admin_role,$user_update);
        $auth->addChild($admin_role,$user_delete);


        $first_user = User::find()->orderBy([
            'id' => SORT_ASC
        ])->one();

        $auth->assign($admin_role, $first_user->id);


    }

}