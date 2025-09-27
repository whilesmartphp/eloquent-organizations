<?php

namespace Whilesmart\Organizations\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Organization extends Model
{
    use HasFactory, Sluggable;

    const TYPE_INDIVIDUAL = 'individual';

    const TYPE_ORGANIZATION = 'organization';

    protected $fillable = [
        'name',
        'slug',
        'type',
        'email',
        'phone',
        'address',
        'website',
        'description',
        'owner_type',
        'owner_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Return the sluggable configuration array for this model.
     */
    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
                'onUpdate' => true,
                'separator' => '-',
                'method' => null,
                'maxLength' => null,
                'maxLengthKeepWords' => true,
                'slugEngineOptions' => [],
                'reserved' => null,
                'unique' => true,
                'includeTrashed' => false,
            ],
        ];
    }

    public function workspace(): BelongsTo
    {
        // Optional workspace relationship
        if (class_exists('Whilesmart\\Workspaces\\Models\\Workspace')) {
            return $this->belongsTo('Whilesmart\\Workspaces\\Models\\Workspace');
        }

        return $this->belongsTo(config('organizations.workspace_model', 'App\\Models\\Workspace'));
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(
            config('organizations.user_model', 'App\\Models\\User'),
            'organization_members',
            'organization_id',
            'user_id'
        )->withTimestamps();
    }

    public function scopeInWorkspace($query, $workspaceId = null)
    {
        if ($workspaceId) {
            return $query->where('workspace_id', $workspaceId);
        }

        return $query;
    }

    public function isIndividual(): bool
    {
        return $this->type === self::TYPE_INDIVIDUAL;
    }

    public function isOrganization(): bool
    {
        return $this->type === self::TYPE_ORGANIZATION;
    }
}
