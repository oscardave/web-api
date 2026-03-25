<?php
declare(strict_types=1);

namespace App\Http\Frontend\Controllers;

use App\Http\Traits\BaseJsonTrait;
use App\Http\Traits\BaseCommonTrait;
// use App\Http\Traits\BaseCURDTrait;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\View\RenderInterface;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;
use App\Http\Frontend\Dao\Users as UsersDao;
class FrontendController
{
    // use BaseCURDTrait;
    use BaseJsonTrait;
    use BaseCommonTrait;

    /**
     * @var array
     */    
    protected static array $validations = [
        'validationName' => [
            'rules' => [],
            'messages' => [],
        ]
    ];
    
    /**
     * 应用名称
     * @var string
     */
    protected static string $appName = 'frontend';

    /**
     * @Inject()
     * @var ValidatorFactoryInterface
     */
    protected $validationFactory;

    /**
     * 系统默认渲染方式
     *
     * @param RenderInterface $render
     * @param RequestInterface $request
     * @param array $data
     * @param string $viewFile
     * @return Psr7ResponseInterface
     */
    protected static function render(RenderInterface $render, RequestInterface $request, array $data = [], string $viewFile = ''): Psr7ResponseInterface
    {
        $controller = $data['controller'] ?? self::getControllerName($request);
        if (!$viewFile) {
            $action = self::getActionName($request);
            $viewFile = $controller . '/' . $action;
        }
        return $render->render(self::$appName . '/' . $viewFile, array_merge([
            'controller' => $controller,
        ], $data));
    }

    /**
     * 得到控制器名称
     * @param RequestInterface $request
     * @return mixed|string
     */
    protected static function getControllerName(RequestInterface $request): string
    {
        $pathInfo = $request->getPathInfo(); // 得到请求路由信息
        $controller = 'index';
        $acInfo = explode('/', trim($pathInfo, '/'));
        if (!empty($acInfo[0])) {
            $controller = $acInfo[0];
        }

        return $controller;
    }

    /**
     * 得到方法名称
     * @param RequestInterface $request
     * @return string
     */
    protected static function getActionName(RequestInterface $request): string
    {
        $pathInfo = $request->getPathInfo(); // 得到请求路由信息
        $action = 'index';
        $acInfo = explode('/', trim($pathInfo, '/'));
        if (!empty($acInfo[1])) {
            $action = $acInfo[1];
        }
        return $action;
    }

    /**
     * @param array $data
     * @param string $validationName
     * @return ?string
     */
    protected function validate(array $data, string $validationName): ?string
    {
        $validator = $this->validationFactory->make(
            $data,
            static::$validations[$validationName]['rules'],
            static::$validations[$validationName]['messages']
        );
        if ($validator->fails()) {
            return $validator->errors()->first();
        }
        return null;
    }

     /**
     * 从请求中获取并验证 access_token
     * @param RequestInterface $request
     * @return array ['error' => string, 'user_id' => string]
     */
    protected function getAuthenticatedUser(RequestInterface $request): array
    {
        $accessToken = $request->header('authorization', '');
        
        // 去掉可能的 Bearer 前缀
        if (strpos($accessToken, 'Bearer ') === 0) {
            $accessToken = substr($accessToken, 7);
        }
        
        if (empty($accessToken)) {
            return ['error' => 'Access token is required', 'user_id' => ''];
        }
        
        $result = UsersDao::getUserByToken($accessToken);
        
        if ($result['error'] !== '') {
            return ['error' => $result['error'], 'user_id' => ''];
        }
        
        $matrixUserId = $result['data']['user_id'];
        
        // 检查 ext_users 表是否存在该用户，如果不存在则初始化
        $initResult = UsersDao::initializeExtUser($matrixUserId);
        if ($initResult['error'] !== '') {
            return ['error' => '用户初始化失败：' . $initResult['error'], 'user_id' => ''];
        }
        
        return ['error' => '', 'user_id' => $matrixUserId];
    }
}
