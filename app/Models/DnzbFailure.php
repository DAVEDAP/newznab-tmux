<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Yadakhov\InsertOnDuplicateKey;

class DnzbFailure extends Model
{
    use InsertOnDuplicateKey;
    /**
     * @var string
     */
    protected $table = 'dnzb_failures';

    /**
     * @var bool
     */
    protected $dateFormat = false;

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = ['release_id', 'users_id', 'failed'];
}
