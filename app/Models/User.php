<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Branch;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;
    public $timestamps = true;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'user_code',
        'identity_card',
        'email',
        'phone',
        'password',
        'activated',
        'fcm_token',
        'role',
        'branch_id',
        'type',
        'username',
        'line_call',
    ];

    public function branch()
    {
        return $this->hasOne(Branch::class, 'id', 'branch_id');
    }

    public function getTotalAttribute()
    {
        return $this::count();
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'assigned_to');
    }

    public function getTotalSubscriptionsAttribute()
    {
        return $this::subscriptions()->count();
    }

    public function scopeSearch($filters = [])
    {
        $user = auth()->user();

        $query = new User();

        if (isset($filters['role']) && is_int($filters['role'])) {
            $query = $query->where('role', $filters['role']);
        }

        if (isset($filters['type']) && is_int($filters['type'])) {
            $query = $query->where('type', $filters['type']);
        }

        if (isset($filters['name'])) {
            $query = $query->where(\DB::raw('lower(name)'), 'like', '%' . strtolower($filters['name']) . '%');
        }

        if (isset($filters['activated']) && is_int($filters['activated'])) {
            $query = $query->where('activated', $filters['activated']);
        }

        if (isset($filters['user_code']) && $filters['user_code'] !== '') {
            $query = $query->where('user_code', $filters['user_code']);
        }

        if ($user->role === config('constants.manager')) {
            // Nếu là quản lý thì lấy tất cả thông tin của nhân viên cùng chi nhánh
            $query = $query->where('branch_id', $user->branch_id);
        } elseif ($user->role === config('constants.employee')) {
            //Nếu là nhân viên thì lấy thông tin của chính mình
            $query = $query->where('id', $user->id);
        } elseif (isset($filters['branch_id']) && is_int($filters['branch_id'])) {
            $query = $query->where('branch_id', $filters['branch_id']);
        }

        $query = $query->with(['branch' => function ($query) {
            $query->select('*');
        }]);

        return $query->orderBy('user_code');
    }
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
