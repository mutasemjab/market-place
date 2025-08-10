@extends('layouts.admin')

@section('css')
<style>
.card-header .card-tools {
    margin-left: auto;
}

.form-group label {
    font-weight: 600;
    color: #495057;
}

.text-danger {
    color: #dc3545 !important;
}

.input-group-text {
    background-color: #e9ecef;
    border-color: #ced4da;
}

.toggle-password {
    border-left: 0;
}

.toggle-password:focus {
    box-shadow: none;
    border-color: #ced4da;
}

.alert-info {
    background-color: #e3f2fd;
    border-color: #90caf9;
    color: #1976d2;
}

.alert-info h6 {
    color: #1976d2;
    margin-bottom: 0.5rem;
}

.alert-info ul li {
    margin-bottom: 0.25rem;
}

h5 {
    color: #495057;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 0.5rem;
}

.btn {
    margin-right: 0.5rem;
}

.btn:last-child {
    margin-right: 0;
}

.form-text {
    font-size: 0.875rem;
}
</style>
@endsection
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('messages.Create_New_User') }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ __('messages.Back_to_Users') }}
                        </a>
                    </div>
                </div>
                
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3">{{ __('messages.Basic_Information') }}</h5>
                                
                                <div class="form-group">
                                    <label for="name">{{ __('messages.Full_Name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name') }}" 
                                           placeholder="{{ __('messages.Enter_full_name') }}" required>
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="email">{{ __('messages.Email_Address') }}</label>
                                    <input type="email" name="email" id="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           value="{{ old('email') }}" 
                                           placeholder="{{ __('messages.Enter_email_address') }}">
                                    <small class="form-text text-muted">{{ __('messages.Email_optional') }}</small>
                                    @error('email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="phone">{{ __('messages.Phone_Number') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" id="phone" 
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           value="{{ old('phone') }}" 
                                           placeholder="{{ __('messages.Enter_phone_number') }}" required>
                                    @error('phone')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="password">{{ __('messages.Password') }} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="password" 
                                               class="form-control @error('password') is-invalid @enderror" 
                                               placeholder="{{ __('messages.Enter_password') }}" required>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary toggle-password" 
                                                    data-target="#password">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        @error('password')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="password_confirmation">{{ __('messages.Confirm_Password') }} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" name="password_confirmation" id="password_confirmation" 
                                               class="form-control" 
                                               placeholder="{{ __('messages.Confirm_password') }}" required>
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary toggle-password" 
                                                    data-target="#password_confirmation">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Account Settings -->
                            <div class="col-md-6">
                                <h5 class="mb-3">{{ __('messages.Account_Settings') }}</h5>
                                
                                <div class="form-group">
                                    <label for="balance">{{ __('messages.Initial_Balance') }}</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input type="number" name="balance" id="balance" 
                                               class="form-control @error('balance') is-invalid @enderror" 
                                               value="{{ old('balance', 0) }}" 
                                               step="0.01" min="0"
                                               placeholder="0.00">
                                        @error('balance')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <small class="form-text text-muted">{{ __('messages.Initial_balance_description') }}</small>
                                </div>

                                <div class="form-group">
                                    <label for="total_points">{{ __('messages.Initial_Points') }}</label>
                                    <input type="number" name="total_points" id="total_points" 
                                           class="form-control @error('total_points') is-invalid @enderror" 
                                           value="{{ old('total_points', 0) }}" 
                                           min="0"
                                           placeholder="0">
                                    @error('total_points')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">{{ __('messages.Initial_points_description') }}</small>
                                </div>

                                <div class="form-group">
                                    <label for="activate">{{ __('messages.Account_Status') }} <span class="text-danger">*</span></label>
                                    <select name="activate" id="activate" class="form-control @error('activate') is-invalid @enderror" required>
                                        <option value="1" {{ old('activate', '1') == '1' ? 'selected' : '' }}>
                                            {{ __('messages.Active') }}
                                        </option>
                                        <option value="2" {{ old('activate') == '2' ? 'selected' : '' }}>
                                            {{ __('messages.Inactive') }}
                                        </option>
                                    </select>
                                    @error('activate')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="referal_code">{{ __('messages.Referral_Code') }}</label>
                                    <div class="input-group">
                                        <input type="text" name="referal_code" id="referal_code" 
                                               class="form-control @error('referal_code') is-invalid @enderror" 
                                               value="{{ old('referal_code') }}" 
                                               placeholder="{{ __('messages.Enter_referral_code') }}">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-primary" id="generateCodeBtn">
                                                <i class="fas fa-random"></i> {{ __('messages.Generate') }}
                                            </button>
                                        </div>
                                        @error('referal_code')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <small class="form-text text-muted">{{ __('messages.Referral_code_description') }}</small>
                                </div>

                              
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle"></i> {{ __('messages.Important_Notes') }}</h6>
                                    <ul class="mb-0">
                                        <li>{{ __('messages.phone_unique_note') }}</li>
                                        <li>{{ __('messages.email_optional_note') }}</li>
                                        <li>{{ __('messages.password_secure_note') }}</li>
                                        <li>{{ __('messages.referral_code_note') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> {{ __('messages.Create_User') }}
                        </button>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
                            {{ __('messages.Cancel') }}
                        </a>
                        <button type="reset" class="btn btn-outline-secondary">
                            <i class="fas fa-undo"></i> {{ __('messages.Reset_Form') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


@endsection


@section('script')
<script>
$(document).ready(function() {
    // Toggle password visibility
    $('.toggle-password').click(function() {
        var target = $(this).data('target');
        var $target = $(target);
        var $icon = $(this).find('i');
        
        if ($target.attr('type') === 'password') {
            $target.attr('type', 'text');
            $icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            $target.attr('type', 'password');
            $icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Generate referral code
    $('#generateCodeBtn').click(function() {
        var $btn = $(this);
        var originalText = $btn.html();
        
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> {{ __("messages.Generating") }}...');
        
        $.ajax({
            url: '{{ route("users.generate-referal-code") }}',
            type: 'GET',
            success: function(response) {
                $('#referal_code').val(response.code);
                showAlert('success', '{{ __("messages.Referral_code_generated") }}');
            },
            error: function() {
                showAlert('error', '{{ __("messages.Error_generating_code") }}');
            },
            complete: function() {
                $btn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Phone number formatting (optional)
    $('#phone').on('input', function() {
        var value = $(this).val();
        // Remove any non-digit characters except + at the beginning
        value = value.replace(/[^\d+]/g, '');
        if (value.indexOf('+') > 0) {
            value = value.replace(/\+/g, '');
            value = '+' + value;
        }
        $(this).val(value);
    });

    // Form validation enhancement
    $('form').on('submit', function() {
        var password = $('#password').val();
        var confirmPassword = $('#password_confirmation').val();
        
        if (password !== confirmPassword) {
            showAlert('error', '{{ __("messages.passwords_do_not_match") }}');
            return false;
        }
        
        if (password.length < 8) {
            showAlert('error', '{{ __("messages.password_min_length") }}');
            return false;
        }
    });
});

function showAlert(type, message) {
    var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
                    message +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                    '<span aria-hidden="true">&times;</span>' +
                    '</button>' +
                    '</div>';
    
    $('.card-body').prepend(alertHtml);
    
    // Auto hide after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
}
</script>
@endsection