<?php
declare(strict_types=1);

namespace App\Http\Traits;

use Hyperf\HttpServer\Contract\RequestInterface;

trait BaseCommonTrait
{
    /**
     * @param RequestInterface $request
     * @return bool
     */
    protected static function isAjax(RequestInterface $request): bool
    {
        $ajaxHeader = $request->header('X-Requested-With');
        return $ajaxHeader && strtolower($ajaxHeader) == 'xmlhttprequest';
    }
}
