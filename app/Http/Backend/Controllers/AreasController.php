<?php
declare(strict_types=1);

namespace App\Http\Backend\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use App\Http\Backend\Dao\AreaDao;
use App\Http\Backend\Validations\AreaValidation;

#[Controller(prefix: "/ht/v1/areas")]
class AreasController extends BackendController
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
                'name' => $data['name'] ?? '',
                'code' => $data['code'] ?? '',
                'city_code' => $data['city_code'] ?? '',
                'province_code' => $data['province_code'] ?? '',
                'status' => (int)($data['status'] ?? 0),
                'page' => (int)($data['page'] ?? 1),
                'pageSize' => (int)($data['pageSize'] ?? 10),
            ];

            $validationError = AreaValidation::validate($requestData);
            if ($validationError !== '') {
                return $this->responseError($validationError);
            }

            $result = AreaDao::list($requestData);
            if ($result['error'] !== '') {
                return $this->responseError($result['error']);
            }

            return $this->responseData($result['data']);
        } catch (\Exception $e) {
            return $this->responseError($e->getMessage());
        }
    }
}
