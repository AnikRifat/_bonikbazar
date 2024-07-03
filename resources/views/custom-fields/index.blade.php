@extends('layouts.main')
@section('title')
    {{__("Custom Fields")}}
@endsection

@section('page-title')
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h4>@yield('title')</h4>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <section class="section">
        <div class="row mb-3">
            <div class="col-md-2 mr-5">
                <select name="category" class="form-select form-control" id="allcategory" aria-label="category">
                    <option value="">{{__("All")}}</option>
                    @include('category.dropdowntree', ['categories' => $categories])
                </select>
            </div>
            <div class="col-md-10">
                <div class="buttons text-end">
                    @can('custom-field-update')
                        <a href="{{ route('custom-fields.create', ['id' => 0]) }}" class="btn btn-primary mb-0">+ {{__("Create Custom Field")}} </a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="col-md-12 col-sm-12">
            <div class="card">
                <div class="card-body">
                    <table class="table table-borderless table-striped" aria-describedby="mydesc" id="table_list"
                           data-toggle="table" data-url="{{ route('custom-fields.show',1) }}" data-click-to-select="true"
                           data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]"
                           data-search="true" data-search-align="right" data-toolbar="#toolbar" data-show-columns="true"
                           data-show-refresh="true" data-fixed-columns="true" data-fixed-number="1" data-fixed-right-number="1"
                           data-trim-on-search="false" data-responsive="true" data-sort-name="id" data-sort-order="desc"
                           data-pagination-successively-size="3" data-query-params="customFieldQueryParams"
                           data-show-export="true" data-export-options='{"fileName": "custom-field-list","ignoreColumn": ["operate"]}' data-export-types="['pdf','json', 'xml', 'csv', 'txt', 'sql', 'doc', 'excel']">
                        <thead class="thead-dark">
                        <tr>
                            <th scope="col" data-field="state" data-checkbox="true"></th>
                            <th scope="col" data-field="id" data-align="center" data-sortable="true">{{ __('ID') }}</th>
                            <th scope="col" data-field="image" data-align="center" data-formatter="imageFormatter">{{ __('Image') }}</th>
                            <th scope="col" data-field="name" data-align="center" data-sortable="true">{{ __('Name') }}</th>
                            <th scope="col" data-field="category_names" data-align="center">{{ __('Category') }}</th>
                            <th scope="col" data-field="type" data-align="center" data-sortable="true">{{ __('Type') }}</th>
                            @canany(['custom-field-update','custom-field-delete'])
                                <th scope="col" data-field="operate" data-sortable="false">{{ __('Action') }}</th>
                            @endcanany
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection
