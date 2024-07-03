@extends('layouts.main')
@section('title')
    {{__("Create Categories")}}
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
        <div class="buttons">
            @if (!empty($category))
                {{--                <a class="btn btn-primary" href="{{ redirect()->back()->getTargetUrl() }}">< {{__("Back to All Categories")}} </a>--}}
                <a class="btn btn-primary" href="{{ route('category.index') }}">< {{__("Back to All Categories")}} </a>
                @can('category-create')
                    <a class="btn btn-primary" href="{{ route('category.create', ['id' => $category->id]) }}">+ {{__("Add Subcategory")}} - /{{ $category->name }} </a>
                @endcanany
            @else
                @can('category-create')
                    <a class="btn btn-primary" href="{{ route('category.create') }}">+ {{__("Add Category")}} </a>
                @endcan
            @endif
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="card-body">
                            <small class="text-danger">* {{__("To change the order, Drag the Table column Up & Down")}}</small>
                            <table class="table table-borderless table-striped" aria-describedby="mydesc"
                                   id="table_list" data-toggle="table" data-url="{{ route('category.show', $category->id ?? 0) }}"
                                   data-click-to-select="true" data-side-pagination="server" data-pagination="true"
                                   data-page-list="[5, 10, 20, 50, 100, 200,500,2000]" data-search="true" data-search-align="right"
                                   data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true"
                                   data-trim-on-search="false" data-responsive="true" data-sort-name="sequence"
                                   data-sort-order="asc" data-pagination-successively-size="3" data-query-params="queryParams"
                                   data-reorderable-rows="true" data-table="categories" data-use-row-attr-func="true" data-mobile-responsive="true"
                                   data-show-export="true" data-export-options='{"fileName": "category-list","ignoreColumn": ["operate"]}' data-export-types="['pdf','json', 'xml', 'csv', 'txt', 'sql', 'doc', 'excel']">
                                <thead class="thead-dark">
                                <tr>
                                    <th scope="col" data-field="id" data-align="center" data-sortable="true">{{ __('ID') }}</th>
                                    <th scope="col" data-field="name" data-align="center" data-sortable="true">{{ __('Name') }}</th>
                                    <th scope="col" data-field="image" data-align="center" data-formatter="imageFormatter">{{ __('Image') }}</th>
                                    <th scope="col" data-field="subcategories_count" data-align="center" data-formatter="subCategoryFormatter" data-sortable="true">{{ __('Subcategories') }}</th>
                                    <th scope="col" data-field="custom_fields_count" data-align="center" data-sortable="true" data-formatter="customFieldFormatter">{{ __('Custom Fields') }}</th>
                                    @can('category-update')
                                        <th scope="col" data-field="status" data-align="center" data-sortable="true" data-formatter="statusSwitchFormatter">{{ __('Active') }}</th>
                                    @endcan
                                    @canany(['category-update', 'category-delete'])
                                        <th scope="col" data-field="operate" data-sortable="false">{{ __('Action') }}</th>
                                    @endcanany
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
