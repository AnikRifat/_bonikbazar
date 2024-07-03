<?php

namespace App\Http\Resources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use JsonSerializable;
use Throwable;

class ItemCollection extends ResourceCollection {
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array|Arrayable|JsonSerializable
     * @throws Throwable
     */
    public function toArray(Request $request) {
        try {
            $response = [];
            foreach ($this->collection as $key => $collection) {
                /* NOTE : This code can be improved */
                $response[$key] = $collection->toArray();
                if ($collection->relationLoaded('featured_items')) {
                    $response[$key]['is_feature'] = count($collection->featured_items) > 0;
                }


                if ($collection->relationLoaded('favourites')) {
                    $response[$key]['total_likes'] = $collection->favourites->count();
                    if (Auth::check()) {
                        $response[$key]['is_liked'] = $collection->favourites->where(['item_id' => $collection->id, 'user_id' => Auth::user()->id])->count() > 0;

                    } else {
                        $response[$key]['is_liked'] = false;
                    }
                }

//                if ($collection->relationLoaded('custom_fields')) {
//                    $response[$key]['custom_fields'] = [];
//
//                    foreach ($collection->custom_fields as $key2 => $customField) {
//                        $response[$key]['custom_fields'][$key2] = $customField->toArray();
//
//                        if ($collection->relationLoaded('item_custom_field_values')) {
//                            $itemCustomFieldValues = $collection->item_custom_field_values->where('custom_field_id', $customField->id)->first();
//                            if ($customField->type == "fileinput") {
//                                $response[$key]['custom_fields'][$key2]['value'] = !empty($itemCustomFieldValues->value) ? [url($itemCustomFieldValues->value)] : [];
//                            } else {
//                                $response[$key]['custom_fields'][$key2]['value'] = $itemCustomFieldValues->value ?? [];
//                            }
//
//                            $response[$key]['custom_fields'][$key2]['custom_field_value'] = !empty($itemCustomFieldValues) ? $itemCustomFieldValues->toArray() : (object)[];
//                            unset($response[$key]['item_custom_field_values']);
//                        }
//                    }
//                }
                if ($collection->relationLoaded('item_custom_field_values')) {
                    $response[$key]['custom_fields'] = [];

                    foreach ($collection->item_custom_field_values as $key2 => $customFieldValue) {
                        if ($customFieldValue->relationLoaded('custom_field')) {
                            if (!empty($customFieldValue->custom_field)) {
                                $response[$key]['custom_fields'][$key2] = $customFieldValue->custom_field->toArray();

                                if ($customFieldValue->custom_field->type == "fileinput") {
                                    $response[$key]['custom_fields'][$key2]['value'] = !empty($customFieldValue->value) ? [url(Storage::url($customFieldValue->value))] : [];
                                } else {
                                    $response[$key]['custom_fields'][$key2]['value'] = $customFieldValue->value ?? [];
                                }

                                $response[$key]['custom_fields'][$key2]['custom_field_value'] = !empty($customFieldValue) ? $customFieldValue->toArray() : (object)[];
                            }

                            unset($response[$key]['custom_fields'][$key2]['custom_field_value']['custom_field']);
                        }
                    }

                    unset($response[$key]['item_custom_field_values']);
                }

                if ($collection->relationLoaded('item_offers') && Auth::check()) {
                    $response[$key]['is_already_offered'] = $collection->item_offers->where('item_id', $collection->id)->where('buyer_id', Auth::user()->id)->count() > 0;
                } else {
                    $response[$key]['is_already_offered'] = false;
                }
            }
            $featuredRows = [];
            $normalRows = [];

            foreach ($response as $key => $value) {
                // ... (Your existing code here)
                // Extracting is_feature condition and processing accordingly
                if ($value['is_feature']) {
                    $featuredRows[] = $value;
                } else {
                    $normalRows[] = $value;
                }
            }


            // Merge the featured rows first and then the normal rows
            $response = array_merge($featuredRows, $normalRows);

            if ($this->resource instanceof AbstractPaginator) {
                //If the resource has a paginated collection then we need to copy the pagination related params and actual item details data will be copied to data params
                return [
                    ...$this->resource->toArray(),
                    'data' => $response
                ];
            }

            return $response;


        } catch (Throwable $th) {
            throw $th;
        }
    }
}
