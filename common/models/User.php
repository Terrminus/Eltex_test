<?php
namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $username
 * @property string $last_name
 * @property string $first_name
 * @property string|null $birth_date
 * @property string|null $comment
 * @property string|null $last_login
 * @property string|null $avatar
 * @property string $auth_key
 * @property string $password_hash
 * @property string|null $password_reset_token
 * @property string $email
 * @property int $status
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $verification_token
 */
class User extends ActiveRecord implements IdentityInterface
{


    const SCENARIO_USER_ADD = 'user_add';
    const SCENARIO_USER_API_ADD = 'user_api_add';
    const SCENARIO_USER_UPDATE = 'user_update';
    const SCENARIO_USER_API_UPDATE = 'user_api_update';

    public $password;
    public $role;
    public $avatar_file;

    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 10;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'value' => date('Y-m-d H:i:s'),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return [
            self::SCENARIO_USER_ADD => ["username","password","last_name","first_name","birth_date","comment","status","email","role","avatar_file"],
            self::SCENARIO_USER_API_ADD => ["username","password","last_name","first_name","birth_date","comment","status","email","role"],
            self::SCENARIO_USER_UPDATE => ["username","password","last_name","first_name","birth_date","comment","status","email","role","avatar_file"],
            self::SCENARIO_USER_API_UPDATE => ["username","password","last_name","first_name","birth_date","comment","status","email","role"],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'last_name', 'first_name', 'auth_key', 'email'], 'required'],
            ['password', 'required', 'on' => self::SCENARIO_USER_ADD],
            ['password', 'required', 'on' => self::SCENARIO_USER_API_ADD],
            ['password', 'string'],
            ['role', 'string'],
            [['birth_date', 'last_login', 'created_at', 'updated_at'], 'safe'],
            [['comment'], 'string'],
            [['status'], 'integer'],
            [['username', 'last_name', 'first_name', 'avatar', 'password_hash', 'password_reset_token', 'email', 'verification_token'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 32],
            [['username'], 'unique'],
            [['email'], 'unique'],
            [['password_reset_token'], 'unique'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE]],
            ['status', 'validateStatus'],
            ['role', 'validateRole'],
            [['avatar_file'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg'],
        ];

    }



    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * @param string $token
     * @param null   $type
     *
     * @return null|\yii\web\IdentityInterface|static
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['id' => $token->getClaim('id')]);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds user by verification email token
     *
     * @param string $token verify email token
     * @return static|null
     */
    public static function findByVerificationToken($token) {
        return static::findOne([
            'verification_token' => $token,
            'status' => self::STATUS_INACTIVE
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        if($password != ''){

            $this->password_hash = Yii::$app->security->generatePasswordHash($password);
            $this->auth_key = Yii::$app->security->generateRandomString();

        }

    }

    public function getPassword(){
        return '';
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Generates new token for email verification
     */
    public function generateEmailVerificationToken()
    {
        $this->verification_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * check if has other active user
     *
     * @return bool
     */
    public function hasOtherActives(){

        if($this->getCurrentRoleTitle() == 'admin'){

            $actives = User::find()
                ->leftJoin('auth_assignment','auth_assignment.user_id=user.id')
                ->where(['user.status'=>self::STATUS_ACTIVE])
                ->andWhere(['auth_assignment.item_name'=>'admin'])
                ->andWhere(['<>','user.id', $this->id])
                ->one();

            if(!$actives) return false;

        }

        return true;
    }

    /**
     * validate user status
     *
     * @param $attribute
     * @param $params
     */
    public function validateStatus($attribute, $params){

        if($this->$attribute == self::STATUS_INACTIVE && $this->id != null){

            if(!$this->hasOtherActives()){

                $this->addError($attribute, 'There must be at least one active admin');

            }
        }


    }
    public function validateRole($attribute, $params){
        $manager = Yii::$app->authManager;
        $role_ids = array_keys($manager->getRoles());

        if(!in_array($this->$attribute,$role_ids)){

            $this->addError($attribute, 'Invalid role');
        }
    }

    /**
     * assign role to user
     * @param $role_name
     */
    public function setRole($role_name){

        if($role_name != ''){

            $manager = Yii::$app->authManager;
            $manager->revokeAll($this->id);
            $new_role = $manager->getRole($role_name);
            $manager->assign($new_role, $this->id);

        }
    }

    /**
     * get title of current role
     *
     * @return int|string
     */
    public function getCurrentRoleTitle(){

        $manager = Yii::$app->authManager;
        $current_user_roles = $manager->getRolesByUser($this->id);

        foreach ($current_user_roles as $role_id=>$role){

            return $role_id;

        }

        return '';
    }
    /**
     * @return \Lcobucci\JWT\Token
     */
    public function generateJWTToken()
    {
        /** @var \sizeg\jwt\Jwt $jwt */
        $jwt = Yii::$app->jwt;
        $signer = $jwt->getSigner('HS256');

        $token = $jwt->getBuilder()
            ->issuedAt(time())
            ->withClaim('id', $this->getId())
            ->expiresAt(time() + Yii::$app->params['token_lifetime'])
            ->getToken($signer, $jwt->getKey());

        return $token;
    }

    /**
     * @return array
     */
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['avatar']);
        unset($fields['auth_key']);
        unset($fields['password_hash']);
        unset($fields['password_reset_token']);
        unset($fields['verification_token']);

        return $fields;
    }

    /**
     * upload avatar_file
     *
     * @return bool
     */
    public function upload()
    {
        if ($this->validate()) {

            if(!is_dir('uploads')){

                mkdir('uploads', 0777);

            }

            if($this->avatar != ''){

                unlink($this->avatar);

            }

            $name = 'uploads/' . $this->avatar_file->baseName . '.' . $this->avatar_file->extension;
            $this->avatar_file->saveAs($name, false);
            $this->avatar = $name;
            return true;

        } else {

            return false;

        }
    }


}
