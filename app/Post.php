<?php

namespace App;

use App\Scopes\PublishedScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Lufficc\Comment\CommentHelper;
use Lufficc\Config\ConfigureHelper;

class Post extends Model
{
    use SoftDeletes, CommentHelper, ConfigureHelper;

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new PublishedScope());
    }

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at', 'published_at'];

    const selectArrayWithOutContent = [
        'id',
        'user_id',
        'category_id',
        'cover_img',
        'title',
        'slug',
        'view_count',
        'description',
        'updated_at',
        'created_at',
        'published_at',
        'status'
    ];

    protected $fillable = ['title', 'description', 'slug', 'category_id', 'user_id', 'content', 'published_at', 'status', 'html_content', 'cover_img'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function configuration()
    {
        return $this->morphOne(Configuration::class, 'configurable');
    }

    public function isPublished()
    {
        return $this->status == 1;
    }

    /**
     * @return array
     */
    public function getConfigKeys()
    {
        return ['allow_resource_comment', 'comment_type', 'comment_info', 'enable_toc'];
    }

    public function getMetaAttribute($value)
    {
        $a = json_decode($value, true);
        return $a ? $a : array();
    }

    public function setMetaInfo($key, $value)
    {
        $meta = $this->meta;
        $meta[$key] = $value;
        $this->meta = json_encode($meta);
    }

    public function toc_enabled()
    {
        $configuration = $this->configuration ? $this->configuration->config : null;
        if (!$configuration) {
            return true; // default is true
        }
        // default is true
        return !isset($configuration['enable_toc']) || (isset($configuration['enable_toc']) && $configuration['enable_toc'] == 'true');
    }
}
