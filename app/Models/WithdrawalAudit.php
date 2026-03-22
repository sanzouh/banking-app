<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawalAudit extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'withdrawals_audit';

    public $guarded = ['*']; // No mass assignable column

    /**
     * Indicates if the model should be timestamped (create and manage created_at and updated_at attributes)
     * @var bool
     */
    public $timestamps = false;

    /**
     * The name of the "created at" column.
     *
     * @var string|null
     */
    public const CREATED_AT = 'created_at';
}
