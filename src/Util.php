<?php

namespace Dan\Shopify;

use Dan\Shopify\Models\AbstractModel;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class Util.
 */
class Util
{
    /**
     * The cache of snake-cased words.
     *
     * @var array
     */
    protected static $snakeCache = [];

    /**
     * The cache of camel-cased words.
     *
     * @var array
     */
    protected static $camelCache = [];

    /**
     * The cache of studly-cased words.
     *
     * @var array
     */
    protected static $studlyCache = [];

    /**
     * Convert a string to snake case.
     *
     * @param  string  $value
     * @param  string  $delimiter
     * @return string
     */
    public static function snake($value, $delimiter = '_')
    {
        $key = $value;

        if (isset(static::$snakeCache[$key][$delimiter])) {
            return static::$snakeCache[$key][$delimiter];
        }

        if (! ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', $value);

            $value = static::lower(preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $value));
        }

        return static::$snakeCache[$key][$delimiter] = $value;
    }

    /**
     * Convert a value to camel case.
     *
     * @param  string  $value
     * @return string
     */
    public static function camel($value)
    {
        if (isset(static::$camelCache[$value])) {
            return static::$camelCache[$value];
        }

        return static::$camelCache[$value] = lcfirst(static::studly($value));
    }

    /**
     * @return mixed
     */
    public static function studly($value)
    {
        $key = $value;

        if (isset(static::$studlyCache[$key])) {
            return static::$studlyCache[$key];
        }

        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return static::$studlyCache[$key] = str_replace(' ', '', $value);
    }

    /**
     * Convert the given string to lower-case.
     *
     * @param  string  $value
     * @return string
     */
    public static function lower($value)
    {
        return mb_strtolower($value, 'UTF-8');
    }

    /**
     * Flatten a multi-dimensional array into a single level.
     *
     * @param  array  $array
     * @param  int  $depth
     * @return array
     */
    public static function flatten($array, $depth = INF)
    {
        return array_reduce($array, function ($result, $item) use ($depth) {
            if (! is_array($item)) {
                return array_merge($result, [$item]);
            } elseif ($depth === 1) {
                return array_merge($result, array_values($item));
            } else {
                return array_merge($result, static::flatten($item, $depth - 1));
            }
        }, []);
    }

    /**
     * @param  string  $myshopify_domain
     * @return string
     */
    public static function normalizeDomain($myshopify_domain)
    {
        $myshopify_domain = preg_replace("/(https:\/\/|http:\/\/)/", '', $myshopify_domain);
        $myshopify_domain = rtrim($myshopify_domain, '/');
        $myshopify_domain = strtolower($myshopify_domain);
        $myshopify_domain = str_replace('.myshopify.com', '', $myshopify_domain);

        return sprintf('%s.myshopify.com', $myshopify_domain);
    }

    /**
     * @param  string  $hmac
     * @param  string  $token
     * @param  string  $data
     * @return bool
     */
    public static function validWebhookHmac($hmac, $token, $data)
    {
        $calculated_hmac = hash_hmac(
            $algorithm = 'sha256',
            $data,
            $token,
            $raw_output = true
        );

        return $hmac == base64_encode($calculated_hmac);
    }

    /**
     * @return bool
     */
    public static function validAppHmac($hmac, $secret, array $data)
    {
        $message = [];

        $keys = array_keys($data);
        sort($keys);
        foreach ($keys as $key) {
            $message[] = "{$key}={$data[$key]}";
        }

        $message = implode('&', $message);

        $calculated_hmac = hash_hmac(
            $algorithm = 'sha256',
            $message,
            $secret
        );

        return $hmac == $calculated_hmac;
    }

    /**
     * @param  int|string|array|\stdClass|\Dan\Shopify\Models\AbstractModel  $mixed
     * @return int|null
     */
    public static function getKeyFromMixed($mixed)
    {
        if (is_numeric($mixed)) {
            return $mixed;
        } elseif (is_array($mixed) && isset($mixed['id'])) {
            return $mixed['id'];
        } elseif ($mixed instanceof \stdClass && isset($mixed->id)) {
            return $mixed->id;
        } elseif ($mixed instanceof AbstractModel) {
            return $mixed->getKey();
        } else {
            return;
        }
    }

    /**
     * @param  string  $client_id
     * @param  string  $client_secret
     * @param  string  $shop
     * @param  string  $code
     * @return array
     */
    public static function appAccessRequest($client_id, $client_secret, $shop, $code)
    {
        $shop = static::normalizeDomain($shop);
        $base_uri = "https://{$shop}/";

        // By default, let's setup our main shopify shop.
        $config = compact('base_uri') + [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json; charset=utf-8;',
            ],
        ];

        $client = new Client($config);
        $json = compact('client_id', 'client_secret', 'code');

        $response = $client->post('admin/oauth/access_token', compact('json'));
        $body = json_decode($response->getBody(), true);

        return $body ?? [];
    }

    /**
     * @param  string  $client_id
     * @param  string  $client_secret
     * @param  string  $shop
     * @param  string  $code
     * @return string|false
     */
    public static function appAccessToken($client_id, $client_secret, $shop, $code)
    {
        $body = static::appAccessRequest($client_id, $client_secret, $shop, $code);

        return $body['access_token'] ?? false;
    }

    /**
     * @param  array  $scopes
     * @param  array  $attributes
     * @return string
     */
    public static function appAuthUrl($shop, $client_id, $redirect_uri, $scopes = [], $attributes = [])
    {
        $shop = static::normalizeDomain($shop);

        $url = $attributes + compact('client_id', 'redirect_uri') + [
            'client_id' => config('services.shopify.app.key'),
            'scope' => implode(',', (array) $scopes),
            'redirect_uri' => config('services.shopify.app.redirect'),
            'state' => md5($shop),
            'grant_options[]' => '',
            'nounce' => 'ok',
        ];

        $url = "https://{$shop}/admin/oauth/authorize?".http_build_query($url);

        return $url;
    }

    /**
     * @return bool
     */
    public static function appValidHmac($hmac, $secret, $data)
    {
        return static::validAppHmac($hmac, $secret, $data);
    }

    /**
     * @return bool
     *              This Package should now ALWAYS be used within Laravel
     */
    public static function isLaravel()
    {
        return true;
    }

    /**
     * @return bool
     */
    public static function isLumen()
    {
        return function_exists('app')
            && preg_match('/lumen/i', app()->version());
    }

    public static function toGid(?string $id, string $resource): ?string
    {
        return Str::startsWith($id, 'gid://')
            ? $id
            : sprintf('gid://shopify/%s/%s', $resource, $id);
    }

    public static function getIdFromGid(?string $id): ?string
    {
        if (empty($id)) {
            return null;
        }

        return Arr::last(explode('/', $id));
    }

    public static function toGraphQLIdParam(?string $id, string $resource): string
    {
        return sprintf('id: "%s"', Util::toGid($id, $resource));
    }

    public static function getGraphQLError(array $response)
    {
        if (Arr::get($response, 'errors')) {
            return Arr::get($response, 'errors.0.message');
        }

        $data = Arr::get($response, 'data');
        if (! $data) {
            return null;
        }

        $first_key = array_key_first($data);
        if (! $first_key) {
            return null;
        }

        return Arr::get($response, sprintf('data.%s.userErrors.0.message', $first_key));
    }

    public static function convertKeysToSnakeCase(array|Collection $collection): array
    {
        if (! $collection instanceof Collection) {
            $collection = collect($collection);
        }

        return $collection->mapWithKeys(function ($value, $key) {
            $snakeKey = Str::snake($key);
            if (is_array($value) || $value instanceof Collection) {

                if (isset($value['edges'])) {
                    $value = filled($value['edges']) ? array_map(fn ($value) => $value['node'], $value['edges']) : [];
                }

                if (isset($value['nodes']) && filled($value['nodes'])) {
                    $value = $value['nodes'];
                }

                $value = static::convertKeysToSnakeCase(collect($value));
            }

            if (is_string($value) && Str::startsWith($value, 'gid://')) {
                $value = static::getIdFromGid($value);
            }

            return [$snakeKey => $value];
        })->toArray();
    }

    public static function convertKeysToCamelCase(array|Collection $collection): array
    {
        if (! $collection instanceof Collection) {
            $collection = collect($collection);
        }

        return $collection->mapWithKeys(function ($value, $key) {
            $camelCase = Str::camel($key);
            if (is_array($value) || $value instanceof Collection) {
                $value = static::convertKeysToCamelCase(collect($value));
            }

            return [$camelCase => $value];
        })->toArray();
    }

    public static function mapFieldsForVariable(array $supportedKeys, array $variables): array
    {
        $variables = Arr::only($variables, array_keys($supportedKeys));
        foreach ($supportedKeys as $key => $map) {
            if (isset($variables[$key])) {
                $variables[$map] = $variables[$key];
            }

            if ($key !== $map) {
                unset($variables[$key]);
            }
        }

        return $variables;
    }

    public static function toMultiDimensionalArray(?array $array)
    {
        if (! $array) {
            return [];
        }

        return is_array(Arr::first($array)) ? $array : [$array];
    }
}
