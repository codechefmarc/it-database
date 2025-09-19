<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model for user roles.
 */
class Role extends Model {
  /**
   * Protected fields from editing on the front-end.
   *
   * @var guarded
   */
  protected $guarded = [
    'id',
    'created_at',
    'updated_at',
  ];

  /**
   * Relationship of roles to users.
   */
  public function users() {
    return $this->hasMany(User::class);
  }

}
