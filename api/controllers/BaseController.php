<?php
namespace api\controllers;

use sizeg\jwt\JwtHttpBearerAuth;
use yii\filters\Cors;
use yii\rest\Controller;
use yii\web\Response;

/**
 * Class BaseController.
 * Main base controller of api
 * @package api\controllers
 */
class BaseController extends Controller
{

    /**
     * behaviors of controllers that extend this. Override parent method
     *
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => JwtHttpBearerAuth::class,
        ];
        $behaviors['contentNegotiator']['formats']['text/html'] = Response::FORMAT_JSON;
        $behaviors['contentNegotiator']['formats']['*/*'] = Response::FORMAT_JSON;
        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => static::allowedDomains(),
                'Access-Control-Request-Method'    => ['GET', 'POST'],
                'Access-Control-Allow-Credentials' => true,
                'Access-Control-Max-Age'           => 3600,
            ],
        ];

        return $behaviors;
    }
    /**
     * List of CORS allowed domains
     *
     * @return array
     */
    public static function allowedDomains()
    {
        return [
            '*',
        ];
    }

    /**
     * standard success response
     *
     * @param null $data
     * @return mixed
     */
    public function createSuccessResponse($data = null)
    {
       $response = ['status' => 'success','data' => $data];
       return $this->serializeData($response);
    }

    /**
     * standard error response
     *
     * @param null $data
     * @return mixed
     */
    public function createErrorResponse($data = null)
    {
        $response = ['status' => 'error','data' => $data];
        return $this->serializeData($response);
    }


}