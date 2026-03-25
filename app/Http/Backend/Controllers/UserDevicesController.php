<?php
declare(strict_types=1);

namespace App\Http\Backend\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use App\Http\Backend\Dao\UserDeviceDao;
use App\Http\Backend\Validations\UserDeviceValidation;

#[Controller(prefix: "/ht/v1/userDevices")]
class UserDevicesController extends BackendController
{
    #[PostMapping(path: "list")]
    public function list(RequestInterface $request)
    {
        try {
            $data = $request->all();
            if (empty($data)) {
                return $this->responseError('无效的请求体');
            }

            $requestData = [
                'username' => $data['username'] ?? '',
                'nickname' => $data['nickname'] ?? '',
                'device_name' => $data['device_name'] ?? '',
                'device_id' => $data['device_id'] ?? '',
                'login_time_start' => $data['login_time_start'] ?? '',
                'login_time_end' => $data['login_time_end'] ?? '',
                'page' => (int)($data['page'] ?? 1),
                'pageSize' => (int)($data['pageSize'] ?? 10),
            ];

            $validationError = UserDeviceValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = UserDeviceDao::list($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseData($result['data']);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }
}
