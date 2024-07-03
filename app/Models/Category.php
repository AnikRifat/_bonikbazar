<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Category extends Model {
    use HasFactory;

    protected $fillable = [
        'name',
        'parent_category_id',
        'image',
        'status'
    ];

    public function subcategories() {
        return $this->hasMany(self::class, 'parent_category_id');
    }

    public function custom_fields() {
        return $this->hasMany(CustomFieldCategory::class);
    }

    public function getImageAttribute($image) {
        if (!empty($image)) {
            return url(Storage::url($image));
        }
        return $image;
    }

    public function items() {
        return $this->hasMany(Item::class);
    }

    public function scopeSearch($query, $search) {
        $search = "%" . $search . "%";
        $query = $query->where(function ($q) use ($search) {
            $q->orWhere('name', 'LIKE', $search)->orWhere('description', 'LIKE', $search);
        });
        return $query;
    }
}
