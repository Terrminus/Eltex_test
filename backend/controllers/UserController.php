<?php
namespace backend\controllers;

use Yii;
use common\models\User;
use backend\models\UserSearch;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * User controller
 */
class UserController extends Controller
{

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => AccessControl::className(),
                'rules' => [
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

            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],

        ];
    }

    /**
     * Lists all User models
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model
     *
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new User model
     * If creation is successful, the browser will be redirected to the 'view' page.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new User();
        $model->setScenario(User::SCENARIO_USER_ADD);

        $roles_list = $this->getRolesList();

        if ($model->load(Yii::$app->request->post())) {

            $model->setPassword($model->password);

            if($model->validate()){

                $model->avatar_file = UploadedFile::getInstance($model, 'avatar_file');
                $model->upload();

                if($model->save()){

                    $model->setRole($model->role);
                    return $this->redirect(['view', 'id' => $model->id]);

                }
            }


        }

        return $this->render('create', [
            'model' => $model,
            'roles_list' => $roles_list
        ]);
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page
     *
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->setScenario(User::SCENARIO_USER_UPDATE);
        $model->role = $model->getCurrentRoleTitle();
        $roles_list = $this->getRolesList();

        if ($model->load(Yii::$app->request->post())) {

            $model->setPassword($model->password);
            $model->setRole($model->role);

            if($model->validate()){

                $model->avatar_file = UploadedFile::getInstance($model, 'avatar_file');
                $model->upload();

                if($model->save()){

                    return $this->redirect(['view', 'id' => $model->id]);
                }
            }


        }

        return $this->render('update', [
            'model' => $model,
            'roles_list' => $roles_list
        ]);
    }

    /**
     * Deletes an existing User model
     * If deletion is successful, the browser will be redirected to the 'index' page
     *
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown
     *
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /**
     * get list of available roles(by current user role) to create/update user
     *
     * @return array
     */
    public function getRolesList(){
        $manager = Yii::$app->authManager;
        $roles_list = [];

        $current_user_roles = $manager->getRoles();

        foreach ($current_user_roles as $role_id=>$role){

            $roles_list[$role_id] = $role_id;

        }


        return $roles_list;
    }

    /**
     * Set user status to ACTIVE
     *
     * @param $id
     * @return \yii\web\Response
     */
    public function actionMakeActive($id){
        $user = $this->findModel($id);
        $user->status = User::STATUS_ACTIVE;
        $user->save();
        return $this->redirect(['view', 'id' => $user->id]);
    }

    /**
     * Set user status to INACTIVE
     * status will be updated only of there is other active users
     *
     * @param $id
     * @return \yii\web\Response
     */
    public function actionMakeInactive($id){
        $user = $this->findModel($id);

        if($user->hasOtherActives()){

            $user->status = User::STATUS_INACTIVE;
            $user->save();

        }
        return $this->redirect(['view', 'id' => $user->id]);
    }
}