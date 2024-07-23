<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusRuleRef extends Model
{
    use HasFactory;
    protected $table = 'bus_rule_ref';

	protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'type',
        'name',
        'rule_name',
        'rule_value',
        'comment',
        'sts_cd',
        'created_at',
        'updated_at',
    ];
}
