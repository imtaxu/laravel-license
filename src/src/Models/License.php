<?php

namespace Imtaxu\LaravelLicense\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsCollection;

class License extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'license_key',
        'status',
        'domain',
        'ip',
        'instance_id',
        'activated_at',
        'expires_at',
        'last_checked_at',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
        'last_checked_at' => 'datetime',
        'metadata' => AsCollection::class,
    ];

    /**
     * Determine if the license is valid.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->expires_at && now()->greaterThan($this->expires_at)) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the license is expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return now()->greaterThan($this->expires_at);
    }

    /**
     * Get the license status label.
     *
     * @return string
     */
    public function getStatusLabelAttribute(): string
    {
        if ($this->isExpired()) {
            return 'Expired';
        }

        return ucfirst($this->status);
    }

    /**
     * Activate the license.
     *
     * @param string $domain
     * @param string $ip
     * @param string $instanceId
     * @return bool
     */
    public function activate(string $domain, string $ip, string $instanceId): bool
    {
        $this->status = 'active';
        $this->domain = $domain;
        $this->ip = $ip;
        $this->instance_id = $instanceId;
        $this->activated_at = now();
        $this->last_checked_at = now();

        return $this->save();
    }

    /**
     * Deactivate the license.
     *
     * @return bool
     */
    public function deactivate(): bool
    {
        $this->status = 'inactive';

        return $this->save();
    }

    /**
     * Update the last checked time.
     *
     * @return bool
     */
    public function markAsChecked(): bool
    {
        $this->last_checked_at = now();

        return $this->save();
    }
}
