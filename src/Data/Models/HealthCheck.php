<?php

namespace codicastudio\Health\Data\Models;

use Illuminate\Database\Eloquent\Model;

class HealthCheck extends Model
{
    protected $table = 'health_checks';

    protected $fillable = [
        'resource_name',
        'resource_slug',
        'target_name',
        'target_slug',
        'target_display',
        'healthy',
        'error_message',
        'runtime',
        'value',
        'value_human',
        'created_at',
    ];

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = null;
    
     /**
     * @var string
     * Set $dateFormat due to Carbon rawCreateFromFormat issue with MS SQL Server datetime format, which includes milliseconds.
     */
    protected $dateFormat = 'Y-m-d H:i:s';
}
