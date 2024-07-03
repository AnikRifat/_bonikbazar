<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Item extends Model {
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'price',
        'description',
        'latitude',
        'longitude',
        'address',
        'contact',
        'show_only_to_premium',
        'video_link',
        'status',
        'user_id',
        'image',
        'country',
        'state',
        'city',
        'all_category_ids'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function category() {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function gallery_images() {
        return $this->hasMany(ItemImages::class);
    }

    public function custom_fields() {
        return $this->hasManyThrough(CustomField::class, CustomFieldCategory::class, 'category_id', 'id', 'category_id', 'custom_field_id');
    }

    public function item_custom_field_values() {
        return $this->hasMany(ItemCustomFieldValue::class);
    }

    public function getImageAttribute($image) {
        if (!empty($image)) {
            return url(Storage::url($image));
        }
        return $image;
    }

    public function featured_items() {
        return $this->hasMany(FeaturedItems::class);
    }

    public function favourites() {
        return $this->hasMany(Favourite::class);
    }

    public function item_offers() {
        return $this->hasMany(ItemOffer::class);
    }

//    protected static function booted() {
//        static::deleting(static function (Item $item) { // before delete() method call this
//            $item->assign_custom_fields()->delete();
//            $item->gallery_images()->delete();
//            // do the rest of the cleanup...
//        });
//    }

    public function scopeSearch($query, $search) {
        $search = "%" . $search . "%";
        $query = $query->where(function ($q) use ($search) {
            $q->orWhere('name', 'LIKE', $search)
                ->orWhere('description', 'LIKE', $search)
                ->orWhere('price', 'LIKE', $search)
                ->orWhere('image', 'LIKE', $search)
                ->orWhere('watermark_image', 'LIKE', $search)
                ->orWhere('latitude', 'LIKE', $search)
                ->orWhere('longitude', 'LIKE', $search)
                ->orWhere('address', 'LIKE', $search)
                ->orWhere('contact', 'LIKE', $search)
                ->orWhere('show_only_to_premium', 'LIKE', $search)
                ->orWhere('status', 'LIKE', $search)
                ->orWhere('video_link', 'LIKE', $search)
                ->orWhere('clicks', 'LIKE', $search)
                ->orWhere('user_id', 'LIKE', $search)
                ->orWhere('country', 'LIKE', $search)
                ->orWhere('state', 'LIKE', $search)
                ->orWhere('city', 'LIKE', $search)
                ->orWhere('category_id', 'LIKE', $search);
        })->orWhereHas('category', function ($q) use ($search) {
            $q->where('name', 'LIKE', $search);
        })->orWhereHas('user', function ($q) use ($search) {
            $q->where('name', 'LIKE', $search);
        });
        return $query;
    }

    public function scopeOwner($query) {
        if (Auth::user()->hasRole('User')) {
            return $query->where('user_id', Auth::user()->id);
        }
        return $query;
    }

    public function scopeApproved($query) {
        return $query->where('status', 'approved');
    }

    public function scopeNotOwner($query) {
        return $query->whereNot('user_id', Auth::user()->id);
    }

    public function scopeSort($query, $column, $order) {
        if ($column == "user_name") {
            return $query->leftJoin('users', 'users.id', '=', 'items.user_id')->orderBy('users.name', $order);
        }
        return $query->orderBy($column, $order);
    }
}
