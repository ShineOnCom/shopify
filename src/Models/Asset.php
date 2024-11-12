<?php

namespace Dan\Shopify\Models;

use Carbon\Carbon;

/**
 * Class Asset.
 *
 * @property string $public_url
 * @property string $content_type
 * @property int $size
 * @property int $theme_id
 * @property array $warnings
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Asset extends AbstractModel
{
    /** @var array */
    public static $omit_on_replication = [
        'id',
        'public_url',
        'content_type',
        'size',
        'theme_id',
        'warnings',
        'created_at',
        'updated_at',
    ];

    /** @var string */
    public static $identifier = 'key';

    /** @var string */
    public static $resource_name = 'asset';

    /** @var string */
    public static $resource_name_many = 'assets';

    /** @var array */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /** @var array */
    protected $casts = [
        'key' => 'string',
        'public_url' => 'string',
        'value' => 'string',
        'content_type' => 'string',
        'size' => 'integer',
        'theme_id' => 'integer',
    ];

    /**
     * @param  array|object  $data
     */
    public function __construct($data = [], $exists = true)
    {
        $data = json_decode(json_encode($data), true);

        $this->fill($data);

        $this->exists = $exists;

        // An identifier doesn't necessarily mean it exists.
        if (isset($data[static::$identifier]) && $exists) {
            $this->syncOriginal();
        }
    }

    /**
     * It'll be groovy if we append `_copy` before the extension.
     *
     * @return static
     */
    public function replicate()
    {
        $attr = $this->getAttributes();

        $dot = strrpos($attr['key'], '.') ?: strlen($attr['key']);
        $key = substr($attr['key'], 0, $dot)
            .'.copy'.substr($attr['key'], $dot);

        $data = compact('key');
        $data += array_diff_key($attr, array_fill_keys(static::$omit_on_replication, null));

        return new static($data);
    }
}
