@extends('layouts.main')

@section('title')
    {{ __('Slider') }}
@endsection

@section('page-title')
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h4>@yield('title')</h4>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first"></div>
        </div>
    </div>
@endsection

@section('content')
    <section class="section">
        <div class="row">
            @can('slider-create')
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-content">
                            <div class="card-body">
                                {!! Form::open(['url' => route('slider.store'), 'files' => true,'class'=>'create-form','id'=>'slider-form','data-pre-submit-function'=>'customValidation']) !!}
                                <div class="row mt-1">
                                    <div class="form-group col-md-12 col-sm-12 mandatory">
                                        {{ Form::label('image', __('Image'), ['class' => 'col-md-12 col-sm-12 col-12 form-label',]) }}
                                        {{ Form::file('image', ['class' => 'form-control', 'accept' => 'image/*','data-parsley-required'=>'true']) }}
                                        @if (count($errors) > 0)
                                            @foreach ($errors->all() as $error)
                                                <div class="alert alert-danger error-msg">{{ $error }}</div>
                                            @endforeach
                                        @endif
                                    </div>

                                    {{ Form::label('item', __('Item'), ['class' => 'col-md-12 col-sm-12 col-form-label','for'=>"items"]) }}
                                    <div class="col-md-12 col-sm-12">
                                        <select name="item" class="form-select form-control-sm select2" id="items" aria-label="items" data-parsley-errors-messages-disabled>
                                            @if (isset($items))
                                                <option value="" selected>{{__("Select Item")}}</option>
                                                @foreach ($items as $row)
                                                    <option value="{{ $row->id }}">{{ $row->name }} </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-12 d-flex justify-content-center align-items-center mt-3">
                                        <h6 class="mb-0">{{__("OR")}}</h6>

                                    </div>
                                    <div class="col-md-12 col-sm-12">
                                        {{ Form::label('third_party_link', __('Third Party Link'), ['class' => 'col-md-12 col-sm-12 col-form-label ',]) }}
                                        {{ Form::text('link', '', [
                                            'class' => 'form-control ',
                                            'placeholder' => __('link'),
                                            'id' => 'link',
                                            'data-parsley-errors-messages-disabled'
                                        ]) }}
                                    </div>
                                    <div class="invalid-form-error-message"></div>
                                    <div class="col-12 d-flex justify-content-end mt-2" style="padding: 1% 2%;">
                                        {{ Form::submit(__('Save'), ['class' => 'btn btn-primary me-1 mb-1']) }}
                                    </div>
                                    {!! Form::close() !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endcan


            <div class="{{\Illuminate\Support\Facades\Auth::user()->can('slider-create') ? "col-md-8" : "col-md-12"}}">
                <div class="card">
                    <div class="card-content">
                        <div class="row mt-1">
                            <div class="card-body">
                                <div class="form-group row ">
                                    <div class="col-12">
                                        <table class="table table-borderless table-striped" aria-describedby="mydesc"
                                               id="table_list" data-toggle="table"
                                               data-url="{{ route('slider.show',1) }}" data-click-to-select="true"
                                               data-side-pagination="server" data-pagination="true"
                                               data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                                               data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true"
                                               data-fixed-columns="true" data-fixed-number="1" data-fixed-right-number="1"
                                               data-trim-on-search="false" data-responsive="true" data-sort-name="id"
                                               data-sort-order="desc" data-pagination-successively-size="3"
                                               data-query-params="queryParams" data-id-field="id"
                                               data-show-export="true" data-export-options='{"fileName": "slider-list","ignoreColumn": ["operate"]}' data-export-types="['pdf','json', 'xml', 'csv', 'txt', 'sql', 'doc', 'excel']">
                                            <thead class="thead-dark">
                                            <tr>
                                                <th scope="col" data-field="id" data-align="center" data-sortable="true">{{ __('ID') }}</th>
                                                <th scope="col" data-field="image" data-align="center" data-sortable="false" data-formatter="imageFormatter">{{ __('Image') }}</th>
                                                <th scope="col" data-field="item.name" data-sort-name="item_name" data-align="center" data-sortable="true">{{ __('Item') }}</th>
                                                <th scope="col" data-field="third_party_link" data-align="center" data-sortable="true">{{ __('Third Party Link') }}</th>
                                                @can('slider-delete')
                                                    <th scope="col" data-field="operate" data-align="center" data-sortable="false">{{ __('Action') }}</th>
                                                @endcan
                                            </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@section('js')
    <script>
        function customValidation() {
            let item = $("select[name=item]").val();
            let link = $("input[name=link]").val();
            // If any of these fields are valid
            if (item != "" && link != "") {
                $('.invalid-form-error-message').html("Please select either of the Fields. Both Fields are not required").addClass("text-danger");
                return false;
            }

            // If any of these fields are valid
            if (item == "" && link == "") {
                // and display a gentle message
                $('.invalid-form-error-message').html("Please select either Item OR Add Link").addClass("text-danger");
                return false;
            }

            // Remove the error message
            $('.invalid-form-error-message').html('');

            return true;
        }
    </script>
@endsection
