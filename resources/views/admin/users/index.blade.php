@extends('layouts.admin')

@section('css')
<style>
.table th {
    background-color: #f8f9fa;
    font-weight: 600;
}

.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

.custom-switch {
    padding-left: 2.25rem;
}

.custom-switch .custom-control-label::before {
    left: -2.25rem;
    width: 1.75rem;
    pointer-events: all;
    border-radius: 0.5rem;
}

.custom-switch .custom-control-label::after {
    top: calc(0.25rem + 2px);
    left: calc(-2.25rem + 2px);
    width: calc(1rem - 4px);
    height: calc(1rem - 4px);
    background-color: #adb5bd;
    border-radius: 0.5rem;
    transition: transform 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.custom-switch .custom-control-input:checked ~ .custom-control-label::after {
    background-color: #fff;
    transform: translateX(0.75rem);
}

.balance-display {
    font-weight: bold;
    color: #28a745;
}

.card-header .card-tools {
    margin-left: auto;
}

code {
    font-size: 0.9rem;
}
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ __('messages.Users_Management') }}</h3>
                    <a href="{{ route('users.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> {{ __('messages.Add_New_User') }}
                    </a>
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <!-- Filter Section -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <input type="text" id="searchInput" class="form-control" placeholder="{{ __('messages.Search_users') }}...">
                        </div>
                        <div class="col-md-2">
                            <select id="statusFilter" class="form-control">
                                <option value="">{{ __('messages.All_Status') }}</option>
                                <option value="1">{{ __('messages.Active') }}</option>
                                <option value="2">{{ __('messages.Inactive') }}</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-secondary" onclick="resetFilters()">
                                <i class="fas fa-refresh"></i> {{ __('messages.Reset') }}
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="usersTable">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.ID') }}</th>
                                    <th>{{ __('messages.Name') }}</th>
                                    <th>{{ __('messages.Email') }}</th>
                                    <th>{{ __('messages.Phone') }}</th>
                                    <th>{{ __('messages.Balance') }}</th>
                                    <th>{{ __('messages.Total_Points') }}</th>
                                    <th>{{ __('messages.Status') }}</th>
                                    <th>{{ __('messages.Referal_Code') }}</th>
                                    <th>{{ __('messages.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr data-user-id="{{ $user->id }}">
                                        <td>{{ $user->id }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email ?: __('messages.Not_Provided') }}</td>
                                        <td>{{ $user->phone }}</td>
                                        <td>
                                            <span class="balance-display">${{ number_format($user->balance, 2) }}</span>
                                            <button type="button" class="btn btn-sm btn-outline-primary ml-1 adjust-balance-btn" 
                                                    data-user-id="{{ $user->id }}" 
                                                    data-current-balance="{{ $user->balance }}"
                                                    title="{{ __('messages.Adjust_Balance') }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                        <td>{{ number_format($user->total_points) }}</td>
                                        <td>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" 
                                                       class="custom-control-input status-toggle" 
                                                       id="status{{ $user->id }}" 
                                                       data-user-id="{{ $user->id }}"
                                                       {{ $user->activate == 1 ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="status{{ $user->id }}">
                                                    <span class="badge badge-{{ $user->activate == 1 ? 'success' : 'danger' }}">
                                                        {{ $user->activate == 1 ? __('messages.Active') : __('messages.Inactive') }}
                                                    </span>
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            @if($user->referal_code)
                                                <code class="bg-light p-1 rounded">{{ $user->referal_code }}</code>
                                            @else
                                                <span class="text-muted">{{ __('messages.No_Referal_Code') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('users.show', $user) }}" 
                                                   class="btn btn-info btn-sm" 
                                                   title="{{ __('messages.View') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('users.edit', $user) }}" 
                                                   class="btn btn-warning btn-sm" 
                                                   title="{{ __('messages.Edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-danger btn-sm delete-user" 
                                                        data-user-id="{{ $user->id }}"
                                                        data-user-name="{{ $user->name }}"
                                                        title="{{ __('messages.Delete') }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">
                                            <p class="mb-0">{{ __('messages.No_users_found') }}</p>
                                            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm mt-2">
                                                {{ __('messages.Create_First_User') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Balance Adjustment Modal -->
<div class="modal fade" id="balanceModal" tabindex="-1" role="dialog" aria-labelledby="balanceModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="balanceModalLabel">{{ __('messages.Adjust_User_Balance') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="balanceForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="currentBalance">{{ __('messages.Current_Balance') }}</label>
                        <input type="text" class="form-control" id="currentBalance" readonly>
                    </div>
                    <div class="form-group">
                        <label for="balanceAction">{{ __('messages.Action') }} <span class="text-danger">*</span></label>
                        <select class="form-control" id="balanceAction" name="action" required>
                            <option value="">{{ __('messages.Select_Action') }}</option>
                            <option value="add">{{ __('messages.Add_Amount') }}</option>
                            <option value="subtract">{{ __('messages.Subtract_Amount') }}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="balanceAmount">{{ __('messages.Amount') }} <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="balanceAmount" name="amount" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label for="balanceReason">{{ __('messages.Reason') }}</label>
                        <textarea class="form-control" id="balanceReason" name="reason" rows="3" placeholder="{{ __('messages.Optional_reason') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('messages.Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('messages.Update_Balance') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Forms (Hidden) -->
@foreach($users as $user)
<form id="deleteForm{{ $user->id }}" action="{{ route('users.destroy', $user) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endforeach


@endsection


@section('script')
<script>
$(document).ready(function() {
    let currentUserId = null;

    // Search functionality
    $('#searchInput').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#usersTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    // Status filter
    $('#statusFilter').on('change', function() {
        var selectedStatus = $(this).val();
        $('#usersTable tbody tr').each(function() {
            var $row = $(this);
            var $statusToggle = $row.find('.status-toggle');
            
            if (selectedStatus === '') {
                $row.show();
            } else {
                var isActive = $statusToggle.is(':checked');
                var statusValue = isActive ? '1' : '2';
                $row.toggle(statusValue === selectedStatus);
            }
        });
    });

    // Status toggle
    $('.status-toggle').change(function() {
        var userId = $(this).data('user-id');
        var $toggle = $(this);
        var $badge = $toggle.siblings('label').find('.badge');
        
        $.ajax({
            url: '{{ route("users.toggle-status", ":id") }}'.replace(':id', userId),
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    if ($toggle.is(':checked')) {
                        $badge.removeClass('badge-danger').addClass('badge-success').text('{{ __("messages.Active") }}');
                    } else {
                        $badge.removeClass('badge-success').addClass('badge-danger').text('{{ __("messages.Inactive") }}');
                    }
                    
                    // Show success message
                    showAlert('success', response.message);
                } else {
                    // Revert toggle if failed
                    $toggle.prop('checked', !$toggle.is(':checked'));
                    showAlert('error', response.message);
                }
            },
            error: function() {
                // Revert toggle if failed
                $toggle.prop('checked', !$toggle.is(':checked'));
                showAlert('error', '{{ __("messages.Error_occurred") }}');
            }
        });
    });

    // Balance adjustment
    $('.adjust-balance-btn').click(function() {
        currentUserId = $(this).data('user-id');
        var currentBalance = $(this).data('current-balance');
        
        $('#currentBalance').val('$' + parseFloat(currentBalance).toFixed(2));
        $('#balanceForm')[0].reset();
        $('#balanceModal').modal('show');
    });

    $('#balanceForm').submit(function(e) {
        e.preventDefault();
        
        var formData = {
            action: $('#balanceAction').val(),
            amount: $('#balanceAmount').val(),
            reason: $('#balanceReason').val(),
            _token: '{{ csrf_token() }}'
        };

        $.ajax({
            url: '{{ route("users.adjust-balance", ":id") }}'.replace(':id', currentUserId),
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Update balance display
                    $('tr[data-user-id="' + currentUserId + '"] .balance-display').text('$' + parseFloat(response.new_balance).toFixed(2));
                    $('tr[data-user-id="' + currentUserId + '"] .adjust-balance-btn').data('current-balance', response.new_balance);
                    
                    $('#balanceModal').modal('hide');
                    showAlert('success', response.message);
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function() {
                showAlert('error', '{{ __("messages.Error_occurred") }}');
            }
        });
    });

    // Delete user
    $('.delete-user').click(function() {
        var userId = $(this).data('user-id');
        var userName = $(this).data('user-name');
        
        if (confirm('{{ __("messages.Are_you_sure_delete_user") }} "' + userName + '"?')) {
            $('#deleteForm' + userId).submit();
        }
    });
});

function resetFilters() {
    $('#searchInput').val('');
    $('#statusFilter').val('');
    $('#usersTable tbody tr').show();
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
    
    // Auto hide after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
}
</script>
@endsection