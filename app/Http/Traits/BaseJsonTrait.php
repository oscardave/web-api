<?php
declare(strict_types=1);

namespace App\Http\Traits;
use Hyperf\Utils\ApplicationContext;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;

trait BaseJsonTrait
{


    /**
     * return json success
     * @return object
     */
    protected static function jsonOk(string $message = ""): object
    {
        $response = ApplicationContext::getContainer()->get(HttpResponse::class);
        return $response->json((object)[
            "code" => 0,
            "message" => $message
        ]);
    }

    /**
     * @param string $message
     * @param int $code
     * @return object
     */
    protected static function jsonExpireErr(string $message = '程序运行出现错误', int $code = 501): object
    {
        $response = ApplicationContext::getContainer()->get(HttpResponse::class);

        return $response->json((object)[
            'code' => $code,
            "message" => $message
        ]);
    }

    /**
     * @param string $message
     * @param int $code
     * @return object
     */
    protected static function jsonErr(string $message = '程序运行出现错误', int $code = 500): object
    {
        $response = ApplicationContext::getContainer()->get(HttpResponse::class);
        return $response->json((object)[
            'code' => $code,
            "message" => $message
        ]);
    }

    /**
     * 默认返回带结果信息
     * @param array $data
     * @param string $message
     * @return object
     */
    protected static function jsonResult(array $data, string $message = ''): object
    {
        return self::jsonArray($data, $message);
    }

    /**
     * 默认返回带结果信息
     * @param array $data
     * @param string $message
     * @return object
     */
    protected static function jsonArray(array $data, string $message = ''): object
    {
        $response = ApplicationContext::getContainer()->get(HttpResponse::class);
        return $response->json([
            'code' => 0,
            'message' => $message,
            'data' => $data
        ]);
    }

    /**
     * 默认返回带结果信息
     * @param object $data
     * @param string $message
     * @return object
     */
    protected static function jsonObject(object $data, string $message = ''): object
    {
        $response = ApplicationContext::getContainer()->get(HttpResponse::class);
        return $response->json([
            'code' => 0,
            'message' => $message,
            'data' => $data
        ]);
    }
}