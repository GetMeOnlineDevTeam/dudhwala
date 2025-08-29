<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OtpCode extends Model
{
    use HasFactory;
    protected $table = 'otp_codes'; // Ensure this matches your migration table name
    protected $fillable = ['user_id', 'otp','is_used', 'expires_at'];

    protected $dates = ['expires_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
