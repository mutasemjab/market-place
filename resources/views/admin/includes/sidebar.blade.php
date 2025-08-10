<!-- Main Sidebar -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('admin.dashboard') }}" class="brand-link">
        <img src="{{ asset('assets/admin/dist/img/AdminLTELogo.png') }}" alt="App Logo"
            class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">Taksi</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- User Panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="info">
              <h4 style="color: white; margin:auto;"> {{ auth()->user()->name }}</h4>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>{{ __('messages.dashboard') }}</p>
                    </a>
                </li>
                
          

                   <li class="nav-item">
                            <a href="{{ route('cities.index') }}" class="nav-link {{ request()->routeIs('cities.*') ? 'active' : '' }}">
                                <i class="fas fa-folder nav-icon"></i>
                                <p>{{ __('messages.cities') }}</p>
                            </a>
                   </li>

                   <li class="nav-item">
                            <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                <i class="fas fa-folder nav-icon"></i>
                                <p>{{ __('messages.users') }}</p>
                            </a>
                   </li>

                   <li class="nav-item">
                            <a href="{{ route('banners.index') }}" class="nav-link {{ request()->routeIs('banners.*') ? 'active' : '' }}">
                                <i class="fas fa-folder nav-icon"></i>
                                <p>{{ __('messages.banners') }}</p>
                            </a>
                   </li>

                   <li class="nav-item">
                            <a href="{{ route('shop-categories.index') }}" class="nav-link {{ request()->routeIs('shop-categories.*') ? 'active' : '' }}">
                                <i class="fas fa-folder nav-icon"></i>
                                <p>{{ __('messages.ShopCategories') }}</p>
                            </a>
                   </li>
                    
                   <li class="nav-item">
                            <a href="{{ route('shops.index') }}" class="nav-link {{ request()->routeIs('shops.*') ? 'active' : '' }}">
                                <i class="fas fa-box nav-icon"></i>
                                <p>{{ __('messages.shops') }}</p>
                            </a>
                    </li>
                   <li class="nav-item">
                            <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                                <i class="fas fa-box nav-icon"></i>
                                <p>{{ __('messages.categories') }}</p>
                            </a>
                    </li>
                   <li class="nav-item">
                            <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                                <i class="fas fa-box nav-icon"></i>
                                <p>{{ __('messages.products') }}</p>
                            </a>
                    </li>
              
                      @if ($user->can('offer-table') || $user->can('offer-add') || $user->can('offer-edit') || $user->can('offer-delete'))
                        <li class="nav-item">
                            <a href="{{ route('offers.index') }}" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p> {{ __('messages.offers') }} </p>
                            </a>
                        </li>
                    @endif
                    @if ($user->can('coupon-table') || $user->can('coupon-add') || $user->can('coupon-edit') || $user->can('coupon-delete'))
                        <li class="nav-item">
                            <a href="{{ route('coupons.index') }}" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p> {{ __('messages.coupons') }} </p>
                            </a>
                        </li>
                    @endif

                    
                    @if ($user->can('order-table') || $user->can('order-add') || $user->can('order-edit') || $user->can('order-delete'))
                        <li class="nav-item">
                            <a href="{{ route('orders.index') }}" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p> {{ __('messages.Orders') }} </p>
                            </a>
                        </li>
                    @endif



                    @if (
                        $user->can('delivery-table') ||
                            $user->can('delivery-add') ||
                            $user->can('delivery-edit') ||
                            $user->can('delivery-delete'))
                        <li class="nav-item">
                            <a href="{{ route('deliveries.index') }}" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p> {{ __('messages.deliveries') }} </p>
                            </a>
                        </li>
                    @endif

                     @if (
                        $user->can('notification-table') ||
                            $user->can('notification-add') ||
                            $user->can('notification-edit') ||
                            $user->can('notification-delete'))
                        <li class="nav-item">
                            <a href="{{ route('notifications.create') }}" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p> {{ __('messages.notifications') }} </p>
                            </a>
                        </li>
                    @endif

                      @if (
                    $user->can('page-table') ||
                        $user->can('page-add') ||
                        $user->can('page-edit') ||
                        $user->can('page-delete'))
                    <li class="nav-item">
                        <a href="{{ route('pages.index') }}" class="nav-link">
                            <i class="far fa-circle nav-icon"></i>
                            <p>{{__('messages.Pages')}} </p>
                        </a>
                    </li>
                    @endif

                <!-- Account -->
                <li class="nav-item">
                    <a href="{{ route('admin.login.edit', auth()->user()->id) }}" class="nav-link {{ request()->routeIs('admin.login.edit') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-user-cog"></i>
                        <p>{{ __('messages.admin_account') }}</p>
                    </a>
                </li>

               
            </ul>
        </nav>
    </div>
</aside>