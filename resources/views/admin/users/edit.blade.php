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

.info-box {
    display: flex;
    align-items: center;
    padding: 0.75rem;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    margin-bottom: 1rem;
}

.info-box-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    margin-right: 1rem;
    color: white;
    font-size: 1.5rem;
}

.info-box-content {
    flex: 1;
}

.info-box-text {
    display: block;
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
}

.info-box-number {
    display: block;
    font-size: 1.25rem;
    font-weight: 600;
    color: #495057;
}

.alert-warning {
    background-color: #fff3cd;
    border-color: #ffecb5;
    color: #856404;
}

.alert-warning h6 {
    color: #856404;
    margin-bottom: 0.5rem;
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
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ __('messages.Edit_User') }}: {{ $user->name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ __('messages.Back_to_Users') }}
                        </a>
                    </div>
                </div>
                
                <form action="{{ route('users.update', $user) }}" method="POST">
                    @csrf
                    @method('PUT')
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
                                           value="{{ old('name', $user->name) }}" 
                                           placeholder="{{ __('messages.Enter_full_name') }}" required>
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="email">{{ __('messages.Email_Address') }}</label>
                                    <input type="email" name="email" id="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           value="{{ old('email', $user->email) }}" 
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
                                           value="{{ old('phone', $user->phone) }}" 
                                           placeholder="{{ __('messages.Enter_phone_number') }}" required>
                                    @error('phone')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="password">{{ __('messages.New_Password') }}</label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="password" 
                                               class="form-control @error('password') is-invalid @enderror" 
                                               placeholder="{{ __('messages.Leave_blank_to_keep_current') }}">
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
                                    <small class="form-text text-muted">{{ __('messages.Password_change_note') }}</small>
                                </div>

                                <div class="form-group">
                                    <label for="password_confirmation">{{ __('messages.Confirm_New_Password') }}</label>
                                    <div class="input-group">
                                        <input type="password" name="password_confirmation" id="password_confirmation" 
                                               class="form-control" 
                                               placeholder="{{ __('messages.Confirm_new_password') }}">
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
                                    <label for="balance">{{ __('messages.Current_Balance') }}</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input type="number" name="balance" id="balance" 
                                               class="form-control @error('balance') is-invalid @enderror" 
                                               value="{{ old('balance', $user->balance) }}" 
                                               step="0.01" min="0"
                                               placeholder="0.00">
                                        @error('balance')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <small class="form-text text-muted">{{ __('messages.Balance_edit_warning') }}</small>
                                </div>

                                <div class="form-group">
                                    <label for="total_points">{{ __('messages.Total_Points') }}</label>
                                    <input type="number" name="total_points" id="total_points" 
                                           class="form-control @error('total_points') is-invalid @enderror" 
                                           value="{{ old('total_points', $user->total_points) }}" 
                                           min="0"
                                           placeholder="0">
                                    @error('total_points')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">{{ __('messages.Points_edit_warning') }}</small>
                                </div>

                                <div class="form-group">
                                    <label for="activate">{{ __('messages.Account_Status') }} <span class="text-danger">*</span></label>
                                    <select name="activate" id="activate" class="form-control @error('activate') is-invalid @enderror" required>
                                        <option value="1" {{ old('activate', $user->activate) == '1' ? 'selected' : '' }}>
                                            {{ __('messages.Active') }}
                                        </option>
                                        <option value="2" {{ old('activate', $user->activate) == '2' ? 'selected' : '' }}>
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
                                               value="{{ old('referal_code', $user->referal_code) }}" 
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
                                    <small class="form-text text-muted">{{ __('messages.Referral_code_edit_note') }}</small>
                                </div>

                                <div class="form-group">
                                    <label for="google_id">{{ __('messages.Google_ID') }}</label>
                                    <input type="text" name="google_id" id="google_id" 
                                           class="form-control @error('google_id') is-invalid @enderror" 
                                           value="{{ old('google_id', $user->google_id) }}" 
                                           placeholder="{{ __('messages.Enter_google_id') }}">
                                    @error('google_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">{{ __('messages.Google_id_edit_note') }}</small>
                                </div>
                            </div>
                        </div>

                        <!-- User Statistics -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="mb-3">{{ __('messages.User_Statistics') }}</h5>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-info">
                                                <i class="fas fa-user"></i>
                                            </span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">{{ __('messages.User_ID') }}</span>
                                                <span class="info-box-number">#{{ $user->id }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-success">
                                                <i class="fas fa-calendar-plus"></i>
                                            </span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">{{ __('messages.Member_Since') }}</span>
                                                <span class="info-box-number">{{ $user->created_at->format('M Y') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-warning">
                                                <i class="fas fa-clock"></i>
                                            </span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">{{ __('messages.Last_Updated') }}</span>
                                                <span class="info-box-number">{{ $user->updated_at->format('M d') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="info-box">
                                            <span class="info-box-icon bg-{{ $user->activate == 1 ? 'success' : 'danger' }}">
                                                <i class="fas fa-{{ $user->activate == 1 ? 'check' : 'times' }}"></i>
                                            </span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">{{ __('messages.Current_Status') }}</span>
                                                <span class="info-box-number">{{ $user->activate == 1 ? __('messages.Active') : __('messages.Inactive') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Warning Notice -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-exclamation-triangle"></i> {{ __('messages.Important_Edit_Notes') }}</h6>
                                    <ul class="mb-0">
                                        <li>{{ __('messages.balance_edit_note') }}</li>
                                        <li>{{ __('messages.points_edit_note') }}</li>
                                        <li>{{ __('messages.password_empty_note') }}</li>
                                        <li>{{ __('messages.status_change_note') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> {{ __('messages.Update_User') }}
                        </button>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">
                            {{ __('messages.Cancel') }}
                        </a>
                        <a href="{{ route('users.show', $user) }}" class="btn btn-info">
                            <i class="fas fa-eye"></i> {{ __('messages.View_User') }}
                        </a>
                        <button type="button" class="btn btn-danger" onclick="deleteUser()">
                            <i class="fas fa-trash"></i> {{ __('messages.Delete_User') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Form (Hidden) -->
<form id="deleteForm" action="{{ route('users.destroy', $user) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>


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

    // Phone number formatting
    $('#phone').on('input', function() {
        var value = $(this).val();
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
        
        if (password && password !== confirmPassword) {
            showAlert('error', '{{ __("messages.passwords_do_not_match") }}');
            return false;
        }
        
        if (password && password.length < 8) {
            showAlert('error', '{{ __("messages.password_min_length") }}');
            return false;
        }
    });
});

function deleteUser() {
    if (confirm('{{ __("messages.Are_you_sure_delete_user") }} "{{ $user->name }}"?')) {
        if (confirm('{{ __("messages.Delete_user_warning") }}')) {
            document.getElementById('deleteForm').submit();
        }
    }
}

function showAlert(type, message) {
    var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade show" role="alert">' +
                    message +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                    '<span aria-hidden="true">&times;</span>' +
                    '</button>' +
                    '</div>';
    
    $('.card-body').prepend(alertHtml);
    
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
}
</script>
@endsection