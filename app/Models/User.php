<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Describes a user and provides user methods.
 */
class User extends Authenticatable {
  use HasFactory, Notifiable;

  /**
   * The attributes that are mass assignable.
   *
   * @var list<string>
   */
  protected $fillable = [
    'first_name',
    'last_name',
    'role_id',
    'email',
    'password',
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var list<string>
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   *   Returns attributes for the user.
   */
  protected function casts(): array {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
    ];
  }

  /**
   * Return the role the user belongs to.
   */
  public function role(): BelongsTo {
    return $this->belongsTo(Role::class);
  }

  /**
   * Tests if a user has a specific role.
   */
  public function hasRole(string $roleName): bool {
    return $this->role->name === $roleName;
  }

  /**
   * Tests if the user is an admin.
   */
  public function isAdmin(): bool {
    return $this->hasRole('admin');
  }

  // /**
  //  * Test if the user can edit.
  //  */
  // public function canEdit(): bool {
  //   return $this->hasRole('admin') || $this->hasRole('data_entry');
  // }

  // /**
  //  * Test if the user is a student.
  //  */
  // public function isStudent(): bool {
  //   return $this->hasRole('student');
  // }

  // /**
  //  * Tests if the user is read only.
  //  */
  // public function isReadOnly(): bool {
  //   return $this->hasRole('read_only');
  // }

}
