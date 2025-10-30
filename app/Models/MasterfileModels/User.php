<?php

namespace App\Models\MasterfileModels;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'employee_id',
        'name',
        'username',
        'password',
        'role',
        'status',
        'bu_assign',
        'theme',
        'managers_key_code',
        'created_by',
        'is_online',
        'last_seen_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class)->withPivot([
            'can_view',
            'can_insert',
            'can_update',
            'can_delete',
            'can_print',
            'can_tag',
            'can_reprint'
        ]);
    }

    public function rolePermissions()
    {
        return $this->hasManyThrough(
            Permission::class,
            Role::class,
            'user_id', // Foreign key on the roles table
            'role_id', // Foreign key on the role_user_permissions table
            'id',      // Local key on the user table
            'id'       // Local key on the role_user_permissions table
        );
    }

    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }

    /**
     * Get messages sent by the user
     */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Get messages received by the user
     */
    public function receivedMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    /**
     * Get all messages (sent and received) for the user
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id')
            ->union($this->hasMany(Message::class, 'receiver_id'));
    }

    /**
     * Get unread messages count
     */
    public function unreadMessagesCount(): int
    {
        return $this->receivedMessages()->whereNull('read_at')->count();
    }

    /**
     * Get conversation with another user
     */
    public function conversationWith(User $user)
    {
        return Message::betweenUsers($this->id, $user->id)
            ->orderBy('created_at', 'asc');
    }

    /**
     * Update user's online status
     */
    public function updateOnlineStatus(bool $isOnline = true): void
    {
        $this->update([
            'is_online' => $isOnline,
            'last_seen_at' => now(),
        ]);
    }

    /**
     * Mark user as offline
     */
    public function markOffline(): void
    {
        $this->updateOnlineStatus(false);
    }

    /**
     * Check if user is online
     */
    public function isOnline(): bool
    {
        return $this->is_online ?? false;
    }

    // public function 
}
