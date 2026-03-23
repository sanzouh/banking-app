<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Client extends Model
{
    
    /** @use HasFactory<\Database\Factories\ClientFactory> */
    use HasFactory, Notifiable;
    
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'account_num';

    /** Le type de la clé primaire */
    protected $keyType = 'integer';
    
    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     * 
     * @var list<string>
     */
    protected $fillable = [
        'account_num',
        'name',
        'balance'
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function withdrawals() {
        return $this->hasMany(Withdrawal::class, 'account_num', 'account_num');
    }
}
