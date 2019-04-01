<?php
namespace Hootlex\Friendships\Models;

use Hootlex\Friendships\Models\FriendshipGroupMembership;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FriendshipGroup
 * @package Hootlex\Friendships\Models
 */
class FriendshipGroup extends Model
{
    /**
     * @var array
     */
    protected $guarded = ['id', 'created_at', 'updated_at'];
    /**
     * @var array
     */
    protected $fillable = ['slug', 'name'];
    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = array())
    {
        $this->table = config('friendships.tables.fr_groups');
        parent::__construct($attributes);
    }
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = str_slug($value);
    }
    /**
     * @return mixed
     */
    public function groupMembers()
    {
        return $this->hasMany(FriendshipGroupMembership::class, 'group_id');
    }
}
