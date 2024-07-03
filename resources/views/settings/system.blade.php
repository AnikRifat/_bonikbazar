@extends('layouts.main')

@section('title')
    {{ __('System Settings') }}
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
        <form class="create-form-without-reset" action="{{route('settings.store') }}" method="post" enctype="multipart/form-data" data-success-function="successFunction" data-parsley-validate>
            @csrf
            <div class="row d-flex mb-3">
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="divider pt-3">
                                <h6 class="divider-text">{{ __('Company Details') }}</h6>
                            </div>
                            <div class="row mt-1">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-sm-12 form-group mandatory">
                                            <label for="company_name" class="col-sm-6 col-md-6 form-label mt-1">{{ __('Company Name') }}</label>
                                            <input name="company_name" type="text" class="form-control" id="company_name" placeholder="{{ __('Company Name') }}" value="{{ $settings['company_name'] ?? '' }}" required>
                                        </div>
                                        <div class="col-sm-12 form-group mandatory">
                                            <label for="company_email" class="col-sm-12 col-md-6 form-label mt-1">{{ __('Email') }}</label>
                                            <input id="company_email" name="company_email" type="email" class="form-control" placeholder="{{ __('Email') }}" value="{{ $settings['company_email'] ?? '' }}" required>
                                        </div>

                                        <div class="col-sm-12 form-group mandatory">
                                            <label for="company_tel1" class="col-sm-12 col-md-6 form-label mt-1">{{ __('Contact Number')." 1" }}</label>
                                            <input id="company_tel1" name="company_tel1" type="text" class="form-control" placeholder="{{ __('Contact Number')." 1" }}" maxlength="16" onKeyDown="if(this.value.length==16 && event.keyCode!=8) return false;" value="{{ $settings['company_tel1'] ?? '' }}" required>
                                        </div>

                                        <div class="col-sm-12">
                                            <label for="company_tel2" class="col-sm-12 col-md-6 form-label mt-1">{{ __('Contact Number')." 2" }}</label>
                                            <input id="company_tel2" name="company_tel2" type="text" class="form-control" placeholder="{{ __('Contact Number')." 2" }}" maxlength="16" onKeyDown="if(this.value.length==16 && event.keyCode!=8) return false;" value="{{ $settings['company_tel2'] ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="divider pt-3">
                                <h6 class="divider-text">{{ __('More Setting') }}</h6>
                            </div>
                            <div class="form-group row mt-3">
                                <label for="default_language" class="col-sm-12 col-md-2 form-check-label ">{{ __('Default Language') }}</label>
                                <div class="col-sm-12 col-md-4 col-xs-12 ">
                                    <select name="default_language" id="default_language" class="form-select form-control-sm">
                                        @foreach ($languages as $row)
                                            {{ $row }}
                                            <option value="{{ $row->code }}"
                                                {{ $settings['default_language'] == $row->code ? 'selected' : '' }}>
                                                {{ $row->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <label for="currency_symbol" class="col-sm-12 col-md-2 form-check-label ">{{ __('Currency Symbol') }}</label>
                                <div class="col-sm-12 col-md-4">
                                    <input id="currency_symbol" name="currency_symbol" type="text" class="form-control" placeholder="{{ __('Currency Symbol') }}" value="{{ $settings['currency_symbol'] ?? '' }}" required="">
                                </div>
                            </div>

                            <div class="form-group row mt-3">
                                <label for="ios_version" class="col-sm-2 form-check-label ">{{ __('IOS Version') }}</label>
                                <div class="col-sm-12 col-md-4">
                                    <input id="ios_version" name="ios_version" type="text" class="form-control" placeholder="{{ __('IOS Version') }}" value="{{ $settings['ios_version'] ?? '' }}" required="">
                                </div>
                                <label for="android_version" class="col-sm-12 col-md-2 form-check-label ">{{ __('Android Version') }}</label>
                                <div class="col-sm-12 col-md-4">
                                    <input id="android_version" name="android_version" type="text" class="form-control" placeholder="{{ __('Android Version') }}" value="{{ $settings['android_version']?? '' }}" required="">
                                </div>
                            </div>
                            <div class="form-group row mt-3">
                                <label class="col-sm-12 col-md-2 form-check-label ">{{ __('Maintenance Mode') }}</label>
                                <div class="col-sm-12 col-md-4">
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="maintenance_mode" id="maintenance_mode" class="checkbox-toggle-switch-input" value="{{ $settings['maintenance_mode'] ?? 0 }}">
                                        <input class="form-check-input checkbox-toggle-switch" type="checkbox" role="switch" {{ $settings['maintenance_mode'] == '1' ? 'checked' : '' }} id="switch_maintenance_mode">
                                        <label class="form-check-label" for="switch_maintenance_mode"></label>
                                    </div>

                                </div>
                                <label for="place_api_key" class="col-sm-12 col-md-2 form-check-label ">{{ __('Place API Key') }}</label>
                                <div class="col-sm-12 col-md-4">
                                    <input id="place_api_key" name="place_api_key" type="text" class="form-control" placeholder="{{ __('Place API Key') }}" value="{{ $settings['place_api_key'] ?? '' }}" required="">
                                </div>
                            </div>
                            <div class="form-group row mt-3">
                                <label class="col-sm-12 col-md-2 form-check-label">{{ __('Force Update') }}</label>
                                <div class="col-sm-12 col-md-4">
                                    <div class="form-check form-switch">
                                        <input type="hidden" name="force_update" id="force_update" class="checkbox-toggle-switch-input" value="{{ $settings['force_update'] ?? 0 }}">
                                        <input class="form-check-input checkbox-toggle-switch" type="checkbox" role="switch" {{ $settings['force_update'] == '1' ? 'checked' : '' }}id="switch_force_update">
                                        <label class="form-check-label" for="switch_force_update"></label>
                                    </div>
                                </div>
                                <label
                                    class="col-sm-12 col-md-2 form-check-label mt-2">{{ __('Number With Suffix') }}</label>
                                <div class="col-sm-12 col-md-1 col-xs-12 ">
                                    <div class="form-check form-switch  ">
                                        <input type="hidden" name="number_with_suffix" id="number_with_suffix" class="checkbox-toggle-switch-input" value="{{ $settings['number_with_suffix'] ?? 0 }}">
                                        <input class="form-check-input checkbox-toggle-switch" type="checkbox" role="switch" {{ $settings['number_with_suffix'] == '1' ? 'checked' : '' }} id="switch_number_with_suffix" aria-label="switch_number_with_suffix">
                                    </div>
                                </div>

                                <label for="fcm_key" class="col-sm-12 col-md-12 col-form-label mt-2">{{ __('Notification FCM Key') }}</label>
                                <div class="col-sm-10 col-md-12 col-xs-12 ">
                                    <textarea id="fcm_key" name="fcm_key" class="form-control mt-3" rows="3" placeholder="{{ __('Notification FCM Key') }}">{{ $settings['fcm_key'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="divider pt-3">
                        <h6 class="divider-text">{{ __('Images') }}</h6>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group row">
                                <label class=" col-form-label ">{{ __('Favicon Icon') }}</label>
                                <div class="">
                                    <input class="filepond" type="file" name="favicon_icon" id="favicon_icon">
                                    <img src="{{ $settings['favicon_icon'] ?? '' }}" data-custom-image="{{asset('assets/images/logo/favicon.png')}}" class="mt-2 favicon_icon" alt="image" style=" height: 31%;width: 21%;">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class=" col-form-label ">{{ __('Company Logo') }}</label>
                            <div class="">
                                <input class="filepond" type="file" name="company_logo" id="company_logo">
                                <img src="{{ $settings['company_logo'] ?? '' }}" data-custom-image="{{asset('assets/images/logo/logo.png')}}" class="mt-2 company_logo" alt="image" style="height: 31%;width: 21%;">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group row">
                                <label class=" col-form-label ">{{ __('Login Page Image') }}</label>
                                <div class="">
                                    <input class="filepond" type="file" name="login_image" id="login_image">
                                    <img src="{{ $settings['login_image'] ?? ''  }}" data-custom-image="{{asset('assets/images/bg/login.jpg')}}" class="mt-2 login_image" alt="image" style="height: 31%;width: 21%;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 d-flex justify-content-end">
                <button type="submit" value="btnAdd" class="btn btn-primary me-1 mb-3">{{ __('Save') }}</button>
            </div>
        </form>
    </section>
@endsection
@section('js')
    <script>
        function successFunction() {
            window.location.reload();
        }
    </script>
@endsection
