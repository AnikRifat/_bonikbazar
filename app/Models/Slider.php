<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Slider extends Model {
    use HasFactory;

    protected $fillable = ['image', 'item_id', 'third_party_link', 'sequence'];

    public function item() {
        return $this->belongsTo(Item::class);
    }

    public function getImageAttribute($image) {
        if (!empty($image)) {
            return url(Storage::url($image));
        }
        return $image;
    }

    public function scopeSearch($query, $search) {
        $search = "%" . $search . "%";
        $query = $query->where(function ($q) use ($search) {
            $q->orWhere('sequence', 'LIKE', $search)
                ->orWhere('third_party_link', 'LIKE', $search)
                ->orWhere('item_id', 'LIKE', $search)
                ->orWhereHas('item', function ($q) use ($search) {
                    $q->where('name', 'LIKE', $search);
                });
        });
        return $query;
    }

    public function scopeSort($query, $column, $order) {
        if ($column == "item_name") {
            return $query->leftjoin('items', 'items.id', '=', 'sliders.item_id')->orderBy('items.name', $order)->select('sliders.*');
        }
        return $query->orderBy($column, $order);
    }
}
