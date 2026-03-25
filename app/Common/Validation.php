<?php
declare(strict_types=1);

namespace App\Common;

use Hyperf\Utils\ApplicationContext;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;

class Validation
{
    /**
     * @var ValidatorFactoryInterface
     */
    private static ValidatorFactoryInterface $validator;

    /**
     * 生成一个校验器
     * @return ValidatorFactoryInterface
     */
    public static function getValidator(): ValidatorFactoryInterface
    {
        if (!self::$validator) {
            $container = ApplicationContext::getContainer();
            self::$validator = $container->get(ValidatorFactoryInterface::class);
        }

        return self::$validator;
    }

    /**
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param bool $firstError
     * @return array
     */
    public static function make(array $data, array $rules, array $messages = [], bool $firstError = true): array
    {
        if (empty($messages)) {
            $messages = self::messages();
        }
        if (self::$validator) {
            $valid = self::$validator->make($data, $rules, $messages);
            if ($valid->fails()) {
                $errors = $valid->errors();
                $error = $firstError ? $errors->first() : $errors;
                return [$error];
            }
            return [];
        }
        return ['校验错误'];
    }

    /**
     * 此处定义错误信息
     * @return array
     */
    public static function messages(): array
    {
        return [];
    }
}
