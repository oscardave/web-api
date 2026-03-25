<?php 
declare(strict_types=1);

namespace App\Http\Frontend\Controllers;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use App\Http\Frontend\Dao\Users as UsersDao;

#[Controller(prefix: "api/v1/customer-service")]
class CustomerServiceController extends FrontendController
{
    /**
     * 获取客服用户列表
     * 返回 is_customer_servcie 为 true 的用户，按 sort 降序排列，最多返回20条
     *
     * @param RequestInterface $request
     * @return mixed
     */
    #[GetMapping(path: "list")]
    public function list(RequestInterface $request): mixed
    {
        $result = UsersDao::getCustomerServiceList();
        
        if ($result['error'] !== '') {
            return self::jsonErr($result['error']);
        }
        
        return self::jsonArray($result['data']);
    }
}
