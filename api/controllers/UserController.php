<?php

namespace api\controllers;

use common\models\LoginForm;
use Yii;
use common\models\User;
use backend\models\UserSearch;
use yii\filters\AccessControl;


class UserController extends BaseController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors =  parent::behaviors();

        $allow_actions = ['auth'];

        $behaviors['authenticator']['except'] = $allow_actions;
        //$behaviors['access']['allowActions']  = $allow_actions;

        $behaviors[] = [
            'class' => AccessControl::className(),
            'rules' => [
                [
                    'actions' => ['auth'],
                    'allow' => true,
                    'roles' => ['@'],
                ],
                [
                    'actions' => ['index'],
                    'allow' => true,
                    'roles' => ['@'],
                ],
                [
                    'actions' => ['view'],
                    'allow' => true,
                    'roles' => ['@'],
                ],
                [
                    'actions' => ['make-active'],
                    'allow' => true,
                    'roles' => ['admin','moderator'],
                ],
                [
                    'actions' => ['make-inactive'],
                    'allow' => true,
                    'roles' => ['admin','moderator'],
                ],
                [
                    'actions' => ['create'],
                    'allow' => true,
                    'roles' => ['admin','creator'],
                ],
                [
                    'actions' => ['update'],
                    'allow' => true,
                    'roles' => ['admin'],
                ],
                [
                    'actions' => ['delete'],
                    'allow' => true,
                    'roles' => ['admin'],
                ],
            ],

        ];
        return $behaviors;

    }

    /**
     * Auth action.
     * method: POST
     * Expects "username" and "password" parameters
     * return token on success auth
     *
     * @return mixed
     */
    public function actionAuth(){

        $credentials = Yii::$app->request->post();

        $model = new LoginForm();
        $model->load(['LoginForm'=>$credentials]);

        if($model->validate()){

            $model->login();
            $user = User::findIdentity(Yii::$app->user->id);
            $user->touch('last_login');

            return $this->createSuccessResponse([
                'user' => $user,
                'token' =>(string) $user->generateJWTToken()
            ]);

        }else {
            return $this->createErrorResponse($model->getErrors());
        }

    }

    /**
     * Lists all User models
     * method: POST
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->createSuccessResponse($dataProvider);

    }

    /**
     * Display single user model
     * method: POST
     * Expects "id" parameter
     *
     * @return mixed
     */
    public function actionView()
    {
        $post = Yii::$app->request->post();

        if(!isset($post['id'])) return $this->createErrorResponse('param id is missing');

        $model = $this->findModel($post['id']);

        if($model){

            return $this->createSuccessResponse($model);

        }
        return $this->createErrorResponse('user not found');
    }

    /**
     * Creates a new User model
     * method: POST
     * Expects parameter "User" filled with user attributes
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new User();
        $model->setScenario(User::SCENARIO_USER_API_ADD);

        if ($model->load(Yii::$app->request->post())) {

            $model->setPassword($model->password);

            if($model->validate()){

                if($model->save()){

                    $model->setRole($model->role);
                    return $this->createSuccessResponse($model);

                }

            } else {

                return $this->createErrorResponse($model->getErrors());

            }


        }

        return $this->createErrorResponse('Invalid or empty parameters');

    }

    /**
     * Updates an existing User model
     * method: POST
     * Expects parameters "id"  and "User" (filled with user attributes)
     *
     * @return mixed
     */
    public function actionUpdate()
    {
        $post = Yii::$app->request->post();

        if(!isset($post['id'])) return $this->createErrorResponse('param id is missing');

        $model = $this->findModel($post['id']);
        $model->setScenario(User::SCENARIO_USER_API_UPDATE);

        if($model) {

            if ($model->load(Yii::$app->request->post())) {

                $model->setPassword($model->password);
                $model->setRole($model->role);

                if ($model->validate()) {

                    if ($model->save()) {

                        return $this->createSuccessResponse($model);
                    }

                } else {

                    return $this->createErrorResponse($model->getErrors());

                }

            }
        }

        return $this->createErrorResponse('user not found');

    }

    /**
     * Deletes an existing User model
     * method: POST
     * Expects "id" parameter
     *
     * @return mixed
     */
    public function actionDelete()
    {
        $post = Yii::$app->request->post();

        if(!isset($post['id'])) return $this->createErrorResponse('param id is missing');

        $model = $this->findModel($post['id']);

        if($model){

            $model->delete();
            return $this->createSuccessResponse('deleted successfully');

        }

        return $this->createErrorResponse('user not found');

    }

    /**
     * Finds the User model based on its primary key value
     *
     * @param $id
     * @return bool|null|static
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {

            return $model;

        }

        return false;

    }

    /**
     * Set user status to ACTIVE
     * method: POST
     * Expects "id" parameter
     *
     * @return mixed
     */
    public function actionMakeActive(){
        $post = Yii::$app->request->post();

        if(!isset($post['id'])) return $this->createErrorResponse('param id is missing');

        $user = $this->findModel($post['id']);

        if($user){

            $user = $this->findModel($post['id']);
            $user->status = User::STATUS_ACTIVE;
            $user->save();
            return $this->createSuccessResponse('user status changed successfully');

        }

        return $this->createErrorResponse('user not found');
    }

    /**
     * Set user status to INACTIVE
     * status will be updated only of there is other active users
     * method: POST
     * Expects "id" parameter
     *
     * @return mixed
     */
    public function actionMakeInactive(){

        $post = Yii::$app->request->post();

        if(!isset($post['id'])) return $this->createErrorResponse('param id is missing');

        $user = $this->findModel($post['id']);

        if($user){

            if($user->hasOtherActives()){

                $user->status = User::STATUS_INACTIVE;
                $user->save();
                return $this->createSuccessResponse('user status changed successfully');

            } else {

                return $this->createSuccessResponse('There must be at least one active admin');

            }

        }

        return $this->createErrorResponse('user not found');
    }



}