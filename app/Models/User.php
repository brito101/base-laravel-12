<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Exception;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use PragmaRX\Google2FA\Google2FA;
use Shetabit\Visitor\Traits\Visitor;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, HasRoles, HasUuids, Notifiable, SoftDeletes, Visitor;

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected array $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'photo',
        'telephone',
        'cell',
        'google2fa_secret_enabled',
        'google2fa_secret',
        'first_access',
    ];

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

    /** 2FA */
    public function generateSecretKey(): string
    {
        $google2fa = new Google2FA;

        try {
            return $google2fa->generateSecretKey();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function getQRCodeInline(): string
    {
        $google2fa = new Google2FA;

        $inlineUrl = $google2fa->getQRCodeUrl(
            env('APP_NAME'),
            $this->email,
            $this->google2fa_secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new ImagickImageBackEnd
        );
        $writer = new Writer($renderer);

        return $writer->writeString($inlineUrl);
    }

    /** JWT */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
