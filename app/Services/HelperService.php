<?php

namespace App\Services;

use App\Models\Category;
use JsonException;

class HelperService {
    /**
     * @param array $data
     * @return bool
     */
    public function changeEnv($data = array()) {
        if (count($data) > 0) {

            // Read .env-file
            $env = file_get_contents(base_path() . '/.env');
            // Split string on every " " and write into array
            $env = explode(PHP_EOL, $env);
            // $env = preg_split('/\s+/', $env);
            $temp_env_keys = [];
            foreach ($env as $env_value) {
                $entry = explode("=", $env_value);
                $temp_env_keys[] = $entry[0];

            }
            // Loop through given data
            foreach ((array)$data as $key => $value) {
                $key_value = $key . "=" . $value;

                if (in_array($key, $temp_env_keys, true)) {
                    // Loop through .env-data
                    foreach ($env as $env_key => $env_value) {
                        // Turn the value into an array and stop after the first split
                        // So it's not possible to split e.g. the App-Key by accident
                        $entry = explode("=", $env_value, 2);
                        // // Check, if new key fits the actual .env-key
                        if ($entry[0] == $key) {

                            // If yes, overwrite it with the new one
                            $env[$env_key] = $key . "=\"" . $value . "\"";

                        } else {
                            // If not, keep the old one
                            $env[$env_key] = $env_value;
                        }
                    }
                } else {
                    $env[] = $key_value;
                }
            }
            // Turn the array back to a String
            $env = implode("\n", $env);

            // And overwrite the .env with the new data
            file_put_contents(base_path() . '/.env', $env);

            return true;
        }

        return false;
    }

    /**
     * @param $categories
     * @param int $level
     * @param string $parentCategoryID
     * @description - This function will return the nested category Option tags using in memory optimization
     * @return mixed
     */
    public static function childCategoryRendering(&$categories, $level = 0, $parentCategoryID = '') {
        // Foreach loop only on the parent category objects
        foreach (collect($categories)->where('parent_category_id', $parentCategoryID) as $key => $category) {
            echo "<option value='$category->id'>" . str_repeat('&nbsp;', $level * 4) . '|-- ' . $category->name . "</option>";
            //Once the parent category object is rendered we can remove the category from the main object so that redundant data can be removed
            $categories->forget($key);

            //Now fetch the subcategories of the main category
            $subcategories = $categories->where('parent_category_id', $category->id);
            if (!empty($subcategories)) {
                //Finally if subcategories are available then call the recursive function & see the magic
                return self::childCategoryRendering($categories, $level + 1, $category->id);
            }
        }

        return false;
    }

    public static function buildNestedChildSubcategoryObject($categories) {
        // Used json_decode & encode simultaneously because i wanted to convert whole nested array into object
        try {
            return json_decode(json_encode(self::buildNestedChildSubcategoryArray($categories), JSON_THROW_ON_ERROR), false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return (object)[];
        }
    }

    private static function buildNestedChildSubcategoryArray($categories) {
        $children = [];
        //First Add Parent Categories to root level in an array
        foreach ($categories->toArray() as $value) {
            if ($value["parent_category_id"] == "") {
                $children[] = $value;
            }
        }

        //Then loop on the Parent Category to find the children categories
        foreach ($children as $key => $value) {
            $children[$key]["subcategories"] = self::findChildCategories($categories->toArray(), $value['id']);
        }
        return $children;
    }


    public static function findChildCategories($arr, $parent) {
        $children = [];
        foreach ($arr as $key => $value) {
            if ($value['parent_category_id'] == $parent) {
                $children[] = $value;
            }
        }
        foreach ($children as $key => $value) {
            $children[$key]['subcategories'] = self::findChildCategories($arr, $value['id']);
        }

        return $children;
    }

    /*
     * Sagar's Code :
     * in this i have approached the reverse object moving & removing.
     * which is not working as of now.
     * but will continue working on this in future as it seems bit optimized approach from the current one
    public static function buildNestedChildSubcategoryObject($categories, $finalCategories = []) {
        echo "<pre>";
        // Foreach loop only on the parent category objects
        if (!empty($finalCategories)) {
            $finalCategories = $categories->whereNull('parent_category_id');
        }
        foreach ($categories->whereNotNull('parent_category_id')->sortByDesc('parent_category_id') as $key => $category) {
            echo "----------------------------------------------------------------------<br>";
            $parentCategoryIndex = $categories->search(function ($data) use ($category) {
                return $data['id'] == $category->parent_category_id;
            });
            if (!$parentCategoryIndex) {
                continue;
            }
            // echo "*** This category will be moved to its parent category object ***<br>";
            // print_r($category->toArray());

            // Once the parent category object is rendered we can remove the category from the main object so that redundant data can be removed
            $categories[$parentCategoryIndex]->subcategories[] = $category->toArray();

            $categories->forget($key);
            echo "<br>*** After all the operation main categories object will look like this ***<br>";
            print_r($categories->toArray());

            if (!empty($categories)) {
                // Finally if subcategories are available then call the recursive function & see the magic
                return self::buildNestedChildSubcategoryObject($categories, $finalCategories);
            }
        }
        return $categories;
    } */


    public static function findParentCategory($category, $finalCategories = []) {
        $category = Category::find($category);

        if (!empty($category)) {
            $finalCategories[] = $category->id;

            if (!empty($category->parent_category_id)) {
                $finalCategories[] = self::findParentCategory($category->id, $finalCategories);
            }
        }


        return $finalCategories;
    }
}
