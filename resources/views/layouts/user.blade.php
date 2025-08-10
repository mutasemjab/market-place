<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Order Line - Marketplace</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/front.css') }}">
    
    <style>
        .loading {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            color: #666;
        }
        
        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #666;
        }
        
        .empty-state h3 {
            margin-bottom: 1rem;
            color: #333;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo-container">
                    <div class="logo">
                        <div class="logo-text">
                            <span class="o-letter">O</span><span class="l-letter">L</span>
                        </div>
                    </div>
                    <div class="tagline">ORDER LINE - {{ $locale == 'ar' ? 'متجرك المتميز' : 'Your Premier Marketplace' }}</div>
                </div>
                
                <!-- City Filter -->
              <div class="filter-container">
                <!-- City Select Dropdown -->
                <div class="city-filter">
                    <select class="city-select" id="cityFilter">
                        <option value="">{{ $locale == 'ar' ? '🌍 جميع المدن' : '🌍 All Cities' }}</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}" {{ $selectedCityId == $city->id ? 'selected' : '' }}>
                                🏙️ {{ $locale == 'ar' ? $city->name_ar : $city->name_en }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Language Switcher -->
                <div class="language-switcher">
                    @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                        <a class="language-link" hreflang="{{ $localeCode }}" href="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}">
                            {{ $properties['native'] }}
                        </a>
                    @endforeach
                </div>
            </div>

            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- Slider Section -->
            <section class="slider-section">
                <div class="slider-container">
                    <div class="slider-wrapper">
                        <div class="slider-track" id="sliderTrack">
                            @forelse($banners as $index => $banner)
                            <div class="slide">
                                <img src="{{ asset('assets/admin/uploads/' . $banner->photo) }}" alt="Banner {{ $index + 1 }}">
                                <div class="slide-overlay">
                                    <h2 style="color: white; font-size: 2rem; text-align: center;">
                                        {{ $locale == 'ar' ? 'مرحباً بكم في أوردر لاين' : 'Welcome to Order Line' }}
                                    </h2>
                                </div>
                            </div>
                            @empty
                            <!-- Default slides if no banners -->
                            <div class="slide">
                                <img src="https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=1200&h=400&fit=crop" alt="Welcome Banner">
                                <div class="slide-overlay">
                                    <h2 style="color: white; font-size: 2rem; text-align: center;">
                                        {{ $locale == 'ar' ? 'مرحباً بكم في أوردر لاين' : 'Welcome to Order Line' }}
                                    </h2>
                                </div>
                            </div>
                            @endforelse
                        </div>
                        
                        <!-- Navigation Arrows -->
                        <button class="slider-nav prev" onclick="changeSlide(-1)">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button class="slider-nav next" onclick="changeSlide(1)">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                        
                        <!-- Indicators -->
                        <div class="slider-indicators" id="sliderIndicators">
                            @for($i = 0; $i < max($banners->count(), 3); $i++)
                            <div class="indicator {{ $i == 0 ? 'active' : '' }}" onclick="currentSlide({{ $i + 1 }})"></div>
                            @endfor
                        </div>
                    </div>
                </div>
            </section>
        </div>
        
        <!-- Categories Navigation -->
        <nav class="categories-nav">
            <div class="container">
                <div class="categories-list" id="categoriesList">
                    @forelse($categories as $index => $category)
                    <button class="category-btn {{ $index == 0 ? 'active' : '' }}" data-category="category-{{ $category->id }}">
                        <img src="{{ asset('assets/admin/uploads/' . $category->photo) }}" 
                             alt="Category Image" 
                             style="width: 24px; height: 24px; object-fit: cover; margin-right: 8px; border-radius: 50%;">
                        {{ $locale == 'ar' ? $category->name_ar : $category->name_en }}
                    </button>
                    @empty
                    <div class="loading">
                        <div class="spinner"></div>
                        <span style="margin-left: 1rem;">{{ $locale == 'ar' ? 'جاري تحميل الفئات...' : 'Loading categories...' }}</span>
                    </div>
                    @endforelse
                </div>
            </div>
        </nav>

        <div class="container">
            <!-- Dynamic Category Section -->
            <section class="category-section active" id="dynamic-category">
                <h2 class="section-title" id="category-title">
                    <div class="section-icon" id="category-icon">
                        @if($categories->count() > 0)
                        <img src="{{ asset('assets/admin/uploads/' . $categories->first()->photo) }}" 
                             alt="Category Icon" 
                             style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        @else
                        <i class="fas fa-store"></i>
                        @endif
                    </div>
                    <span id="category-name">
                        @if($categories->count() > 0)
                        {{ $locale == 'ar' ? $categories->first()->name_ar : $categories->first()->name_en }}
                        @else
                        {{ $locale == 'ar' ? 'جاري التحميل...' : 'Loading...' }}
                        @endif
                    </span>
                </h2>
                
                <div class="stores-grid" id="products-container">
                    @forelse($firstCategoryProducts as $product)
                    <div class="store-card">
                        <div class="store-header">
                            <div class="store-logo">
                                <img src="{{ asset('assets/admin/uploads/' . $product->photo) }}" 
                                     alt="Product Image" 
                                     style="width: 80px; height: 80px; object-fit: cover; border-radius: 15px;">
                            </div>
                            <div class="store-info">
                                <h3>{{ $locale == 'ar' ? $product->name_ar : $product->name_en }}</h3>
                                <div class="store-rating">
                                    @php
                                    $rating = $product->number_of_rating ?? 4.5;
                                    $reviews = $product->number_of_review ?? 0;
                                    $fullStars = floor($rating);
                                    $hasHalfStar = ($rating - $fullStars) >= 0.5;
                                    @endphp
                                    
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= $fullStars)
                                            ★
                                        @elseif($i == $fullStars + 1 && $hasHalfStar)
                                            ☆
                                        @else
                                            ☆
                                        @endif
                                    @endfor
                                    {{ number_format($rating, 1) }} ({{ $reviews }} {{ $locale == 'ar' ? 'تقييم' : 'reviews' }})
                                </div>
                            </div>
                        </div>
                        <p class="store-description">
                            {{ $locale == 'ar' ? ($product->description_ar ?? 'منتج عالي الجودة') : ($product->description_en ?? 'High quality product') }}
                        </p>
                        <div class="store-tags">
                            @php
                            $specifications = $locale == 'ar' ? $product->specification_ar : $product->specification_en;
                            $tags = [];
                            
                            if ($specifications) {
                                if (is_string($specifications)) {
                                    $decoded = json_decode($specifications, true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                        $tags = $decoded;
                                    } else {
                                        $tags = array_filter(explode(',', $specifications));
                                    }
                                } elseif (is_array($specifications)) {
                                    $tags = $specifications;
                                }
                            }
                            
                            $tags = array_filter(array_map('trim', $tags));
                            if (empty($tags)) {
                                $tags = $locale == 'ar' ? 
                                    ['منتج مميز', 'جودة عالية', 'توصيل سريع'] : 
                                    ['Featured', 'Quality', 'Fast Delivery'];
                            }
                            @endphp
                            @foreach(array_slice($tags, 0, 3) as $tag)
                            <span class="store-tag">{{ $tag }}</span>
                            @endforeach
                        </div>
                        <div class="store-footer">
                            <span class="delivery-time">
                                🚚 {{ $product->time_of_delivery ?? ($locale == 'ar' ? '30-45 دقيقة' : '30-45 min') }}
                            </span>
                            @if(!empty($product->url))
                            <a href="{{ $product->url }}" target="_blank" style="text-decoration: none;">
                                <button class="store-btn">{{ $locale == 'ar' ? 'اطلب الآن' : 'Order Now' }}</button>
                            </a>
                            @else
                            <button class="store-btn" onclick="alert('{{ $locale == 'ar' ? 'رابط المنتج غير متوفر حالياً' : 'Product link not available' }}')">
                                {{ $locale == 'ar' ? 'اطلب الآن' : 'Order Now' }}
                            </button>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="empty-state">
                        <h3>{{ $locale == 'ar' ? 'لا توجد منتجات في هذه الفئة حالياً' : 'No products available in this category yet' }}</h3>
                        <p>{{ $locale == 'ar' ? 'تحقق مرة أخرى قريباً!' : 'Check back soon!' }}</p>
                    </div>
                    @endforelse
                </div>
            </section>

            <!-- Hidden data for JavaScript -->
            <script>
                window.categoriesData = {
                    @foreach($categories as $category)
                    @php
                    $products = App\Models\Shop::where('category_id', $category->id);
                    if ($selectedCityId) {
                        $products->where('city_id', $selectedCityId);
                    }
                    $products = $products->get();
                    @endphp
                    '{{ $category->id }}': {
                        name: '{{ $locale == 'ar' ? addslashes($category->name_ar) : addslashes($category->name_en) }}',
                        photo: '{{ asset('assets/admin/uploads/' . $category->photo) }}',
                        products: [
                            @foreach($products as $product)
                            {
                                name: '{{ $locale == 'ar' ? addslashes($product->name_ar) : addslashes($product->name_en) }}',
                                description: '{{ $locale == 'ar' ? addslashes($product->description_ar ?? 'منتج عالي الجودة') : addslashes($product->description_en ?? 'High quality product') }}',
                                photo: '{{ asset('assets/admin/uploads/' . $product->photo) }}',
                                rating: {{ $product->number_of_rating ?? 4.5 }},
                                reviews: {{ $product->number_of_review ?? 0 }},
                                specifications: @json($locale == 'ar' ? $product->specification_ar : $product->specification_en),
                                delivery_time: '{{ $product->time_of_delivery ?? ($locale == 'ar' ? '30-45 دقيقة' : '30-45 min') }}',
                                url: '{{ $product->url ?? '' }}'
                            },
                            @endforeach
                        ]
                    },
                    @endforeach
                };
                window.locale = '{{ $locale }}';
                window.csrfToken = '{{ csrf_token() }}';
                window.selectedCityId = '{{ $selectedCityId }}';
                window.baseUrl = '{{ url('/') }}';
            </script>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-wave"></div>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Order Line</h3>
                    <p>{{ $locale == 'ar' ? 'متجرك المتميز الذي يربطك بأفضل المتاجر والخدمات المحلية. منتجات عالية الجودة، توصيل سريع، خدمة استثنائية.' : 'Your premier marketplace connecting you with the best local stores and services. Quality products, fast delivery, exceptional service.' }}</p>
                </div>
                <div class="footer-section">
                    <h3>{{ $locale == 'ar' ? 'تواصل معنا' : 'Contact Us' }}</h3>
                    <a href="mailto:info@orderlinejo.com" class="contact-link">
                        <div class="contact-icon">📧</div>
                        info@orderlinejo.com
                    </a>
                    <a href="tel:+962797540355" class="contact-link">
                        <div class="contact-icon">📞</div>
                        +962797540355
                    </a>
                    <a href="https://wa.me/962797540355" target="_blank" class="contact-link">
                        <div class="contact-icon" style="background: linear-gradient(135deg, #25d366, #128c7e);">💬</div>
                        {{ $locale == 'ar' ? 'واتساب' : 'WhatsApp' }}
                    </a>
                </div>
                <div class="footer-section">
                    <h3>{{ $locale == 'ar' ? 'تابعنا' : 'Connect With Us' }}</h3>
                    <a href="https://www.facebook.com/profile.php?id=61574274808621" target="_blank" class="social-link">
                        <div class="social-icon facebook-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </div>
                        Facebook
                    </a>
                    <a href="https://www.instagram.com/order.linejo/" target="_blank" class="social-link">
                        <div class="social-icon instagram-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </div>
                        Instagram
                    </a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Order Line. {{ $locale == 'ar' ? 'جميع الحقوق محفوظة.' : 'All rights reserved.' }}</p>
            </div>
        </div>
    </footer>

    <script>
        // Global variables
        let currentSlideIndex = 0;
        let slideInterval;
        let currentCityId = window.selectedCityId || null;
        let categoriesData = window.categoriesData || {};
        let currentCategoryId = null;

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Page loaded, initializing...');
            console.log('Current city ID:', currentCityId);
            console.log('Categories data:', categoriesData);
            
            initializeSlider();
            setupCategoryButtons();
            setupCityFilter();
            attachRippleEffects();
        });

        // City filter functionality
       function setupCityFilter() {
    const cityFilter = document.getElementById('cityFilter');
    console.log('Setting up city filter...');
    
    cityFilter.addEventListener('change', async function() {
        const selectedCityId = this.value || null;
        console.log('City filter changed:', selectedCityId);
        currentCityId = selectedCityId;
        
        // Show loading state
        const categoriesList = document.getElementById('categoriesList');
        const productsContainer = document.getElementById('products-container');
        
        categoriesList.innerHTML = `
            <div class="loading">
                <div class="spinner"></div>
                <span style="margin-left: 1rem;">${window.locale === 'ar' ? 'جاري تحميل الفئات...' : 'Loading categories...'}</span>
            </div>
        `;
        
        productsContainer.innerHTML = `
            <div class="loading">
                <div class="spinner"></div>
                <span style="margin-left: 1rem;">${window.locale === 'ar' ? 'جاري تحميل المنتجات...' : 'Loading products...'}</span>
            </div>
        `;

        try {
            // Use the correct route for filtering categories by city
            let url = `${window.baseUrl}/filter-categories`;
            if (selectedCityId) {
                url += `?city_id=${selectedCityId}`;
            }
            
            console.log('Fetching from URL:', url);

            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            console.log('Response status:', response.status);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            console.log('Received data:', data);
            
            if (data.success) {
                // Update categories
                updateCategoriesDisplay(data.categories);
                
                // Update products for first category
                if (data.firstCategoryProducts) {
                    updateProductsDisplay(data.firstCategoryProducts);
                }
                
                // Update category display
                if (data.firstCategory) {
                    updateCategoryDisplay(data.firstCategory);
                    currentCategoryId = data.firstCategory.id;
                }
                
                // Update global data - convert array to object with ID keys
                categoriesData = {};
                if (data.categories && Array.isArray(data.categories)) {
                    data.categories.forEach(category => {
                        categoriesData[category.id] = category;
                    });
                }
                
                // Setup category buttons again
                setupCategoryButtons();
                
            } else {
                throw new Error(data.message || 'Unknown error occurred');
            }

        } catch (error) {
            console.error('Error filtering by city:', error);
            showError(window.locale === 'ar' ? 'فشل في تصفية البيانات. يرجى المحاولة مرة أخرى.' : 'Failed to filter data. Please try again.');
        }
    });
}

        function updateCategoriesDisplay(categories) {
            const categoriesList = document.getElementById('categoriesList');
            
            if (categories && categories.length > 0) {
                categoriesList.innerHTML = '';
                
                categories.forEach((category, index) => {
                    const categoryBtn = document.createElement('button');
                    categoryBtn.className = `category-btn ${index === 0 ? 'active' : ''}`;
                    categoryBtn.setAttribute('data-category', `category-${category.id}`);
                    
                    categoryBtn.innerHTML = `
                        <img src="${category.photo}" 
                             alt="Category Image" 
                             style="width: 24px; height: 24px; object-fit: cover; margin-right: 8px; border-radius: 50%;">
                        ${category.name}
                    `;
                    
                    categoryBtn.addEventListener('click', function() {
                        selectCategory(category.id, this);
                    });
                    
                    categoriesList.appendChild(categoryBtn);
                });
            } else {
                categoriesList.innerHTML = `
                    <div class="empty-state">
                        <h3>${window.locale === 'ar' ? 'لا توجد فئات متاحة' : 'No categories available'}</h3>
                        <p>${window.locale === 'ar' ? 'يرجى اختيار مدينة أخرى أو المحاولة لاحقاً.' : 'Please try selecting a different city or check back later.'}</p>
                    </div>
                `;
            }
        }

        function updateProductsDisplay(products) {
            const productsContainer = document.getElementById('products-container');
            
            if (products && products.length > 0) {
                productsContainer.innerHTML = '';
                
                products.forEach((product, index) => {
                    const productCard = createProductCard(product);
                    productCard.style.animationDelay = `${index * 0.1}s`;
                    productsContainer.appendChild(productCard);
                });
                
                // Reattach ripple effects
                attachRippleEffects();
            } else {
                productsContainer.innerHTML = `
                    <div class="empty-state">
                        <h3>${window.locale === 'ar' ? 'لا توجد منتجات متاحة' : 'No products available'}</h3>
                        <p>${window.locale === 'ar' ? 'لا توجد منتجات في هذه الفئة للمدينة المحددة. تحقق مرة أخرى قريباً!' : 'No products found in this category for the selected city. Check back soon!'}</p>
                    </div>
                `;
            }
        }

        function updateCategoryDisplay(category) {
            const categoryIcon = document.getElementById('category-icon');
            const categoryName = document.getElementById('category-name');

            if (category.photo) {
                categoryIcon.innerHTML = `<img src="${category.photo}" alt="Category Icon" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">`;
            } else {
                categoryIcon.innerHTML = `<i class="fas fa-store"></i>`;
            }

            categoryName.textContent = category.name;
        }

        // Category functionality
        function setupCategoryButtons() {
            const categoryBtns = document.querySelectorAll('.category-btn');
            console.log('Setting up category buttons:', categoryBtns.length);
            
            categoryBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const categoryId = this.getAttribute('data-category').replace('category-', '');
                    console.log('Category clicked:', categoryId);
                    selectCategory(categoryId, this);
                });
            });

            // Set first category as current if available
            if (categoryBtns.length > 0) {
                const firstCategoryId = categoryBtns[0].getAttribute('data-category').replace('category-', '');
                currentCategoryId = firstCategoryId;
                console.log('Current category ID set to:', currentCategoryId);
            }
        }

          

        function updateCategoryContent(categoryId) {
            console.log('Updating category content for:', categoryId);
            console.log('Current city ID:', currentCityId);
            
            // If we're filtering by city, use API call
            if (currentCityId) {
                loadCategoryProductsViaAPI(categoryId);
            } else {
                // Use existing local data
                const categoryData = categoriesData[categoryId];
                if (!categoryData) {
                    console.log('No category data found for:', categoryId);
                    return;
                }

                console.log('Using local data for category:', categoryData);

                // Update category title and icon
                const categoryIcon = document.getElementById('category-icon');
                const categoryName = document.getElementById('category-name');
                const productsContainer = document.getElementById('products-container');

                // Update icon
                categoryIcon.innerHTML = `<img src="${categoryData.photo}" alt="Category Icon" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">`;
                
                // Update name
                categoryName.innerHTML = categoryData.name;

                // Update products with animation
                productsContainer.style.opacity = '0.5';
                productsContainer.style.transform = 'translateY(20px)';

                setTimeout(() => {
                    // Clear current products
                    productsContainer.innerHTML = '';

                    if (categoryData.products.length === 0) {
                        // Show empty state
                        productsContainer.innerHTML = `
                            <div class="empty-state">
                                <h3>${window.locale === 'ar' ? 'لا توجد منتجات في هذه الفئة حالياً' : 'No products available in this category yet'}</h3>
                                <p>${window.locale === 'ar' ? 'تحقق مرة أخرى قريباً!' : 'Check back soon!'}</p>
                            </div>
                        `;
                    } else {
                        // Add new products
                        categoryData.products.forEach(product => {
                            const productCard = createProductCard(product);
                            productsContainer.appendChild(productCard);
                        });
                    }

                    // Animate in
                    productsContainer.style.opacity = '1';
                    productsContainer.style.transform = 'translateY(0)';

                    // Reattach ripple effects to new cards
                    attachRippleEffects();
                }, 150);
            }
        }

      async function loadCategoryProductsViaAPI(categoryId) {
    const productsContainer = document.getElementById('products-container');
    console.log('=== DEBUG: Starting loadCategoryProductsViaAPI ===');
    console.log('Category ID:', categoryId);
    console.log('Current City ID:', currentCityId);
    console.log('Products Container:', productsContainer);
    
    // Check if container exists
    if (!productsContainer) {
        console.error('ERROR: products-container element not found!');
        return;
    }
    
    try {
        // Show loading
        productsContainer.innerHTML = `
            <div class="loading">
                <div class="spinner"></div>
                <span style="margin-left: 1rem;">${window.locale === 'ar' ? 'جاري تحميل المنتجات...' : 'Loading products...'}</span>
            </div>
        `;
        console.log('Loading state displayed');

        // Construct URL
        let url;
        if (window.apiRoutes && window.apiRoutes.categoryProducts) {
            url = `${window.apiRoutes.categoryProducts}?category_id=${categoryId}`;
            if (currentCityId) {
                url += `&city_id=${currentCityId}`;
            }
        } else {
            url = `${window.baseUrl}/category-products?category_id=${categoryId}`;
            if (currentCityId) {
                url += `&city_id=${currentCityId}`;
            }
        }
        
        console.log('Fetching from URL:', url);

        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': window.csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Response not OK:', errorText);
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log('=== RECEIVED DATA ===');
        console.log('Full response:', data);
        console.log('Success:', data.success);
        console.log('Products array:', data.products);
        console.log('Products count:', data.products ? data.products.length : 'undefined');

        if (data.success) {
            console.log('=== PROCESSING PRODUCTS ===');
            
            if (data.products && data.products.length > 0) {
                console.log('Products found, creating cards...');
                
                // Clear container
                productsContainer.innerHTML = '';
                console.log('Container cleared');
                
                // Process each product
                data.products.forEach((product, index) => {
                    console.log(`Processing product ${index + 1}:`, product);
                    
                    try {
                        const productCard = createProductCard(product);
                        console.log('Product card created:', productCard);
                        
                        productCard.style.animationDelay = `${index * 0.1}s`;
                        productsContainer.appendChild(productCard);
                        console.log(`Product ${index + 1} added to container`);
                        
                    } catch (cardError) {
                        console.error('Error creating product card:', cardError);
                        console.error('Product data that caused error:', product);
                    }
                });
                
                console.log('Final container HTML length:', productsContainer.innerHTML.length);
                console.log('Final container children count:', productsContainer.children.length);
                
                // Update category display if provided
                if (data.category) {
                    console.log('Updating category display:', data.category);
                    updateCategoryDisplay(data.category);
                }
                
            } else {
                console.log('No products found, showing empty state');
                productsContainer.innerHTML = `
                    <div class="empty-state">
                        <h3>${window.locale === 'ar' ? 'لا توجد منتجات متاحة' : 'No products available'}</h3>
                        <p>${window.locale === 'ar' ? 'لا توجد منتجات في هذه الفئة للمدينة المحددة.' : 'No products found in this category for the selected city.'}</p>
                    </div>
                `;
            }
        } else {
            console.error('API returned success: false');
            throw new Error(data.message || 'API returned success: false');
        }
        
        // Reattach ripple effects
        console.log('Reattaching ripple effects...');
        attachRippleEffects();
        console.log('=== DEBUG: Completed successfully ===');
        
    } catch (error) {
        console.error('=== ERROR in loadCategoryProductsViaAPI ===');
        console.error('Error object:', error);
        console.error('Error message:', error.message);
        console.error('Error stack:', error.stack);
        
        productsContainer.innerHTML = `
            <div class="empty-state">
                <h3>${window.locale === 'ar' ? 'خطأ' : 'Error'}</h3>
                <p>${window.locale === 'ar' ? 'فشل في تحميل المنتجات. يرجى المحاولة مرة أخرى.' : 'Failed to load products. Please try again.'}</p>
                <small style="color: #999; display: block; margin-top: 10px;">Error: ${error.message}</small>
            </div>
        `;
    }
}

        function createProductCard(product) {
    console.log('=== Creating product card ===');
    console.log('Product data:', product);
    
    // Validate required fields
    if (!product) {
        console.error('Product is null or undefined');
        throw new Error('Product data is missing');
    }
    
    if (!product.name) {
        console.error('Product name is missing:', product);
        throw new Error('Product name is required');
    }
    
    const card = document.createElement('div');
    card.className = 'store-card';
    console.log('Card element created');

    // Generate star rating
    const rating = product.rating || 4.5;
    const fullStars = Math.floor(rating);
    const hasHalfStar = (rating - fullStars) >= 0.5;
    let starsHtml = '';
    
    for (let i = 1; i <= 5; i++) {
        if (i <= fullStars) {
            starsHtml += '★';
        } else if (i === fullStars + 1 && hasHalfStar) {
            starsHtml += '☆';
        } else {
            starsHtml += '☆';
        }
    }
    console.log('Stars HTML generated:', starsHtml);

    // Handle specifications
    let tags = [];
    if (product.specifications) {
        if (Array.isArray(product.specifications)) {
            tags = product.specifications;
        } else if (typeof product.specifications === 'string') {
            try {
                const parsed = JSON.parse(product.specifications);
                if (Array.isArray(parsed)) {
                    tags = parsed;
                } else {
                    tags = product.specifications.split(',');
                }
            } catch (e) {
                tags = product.specifications.split(',');
            }
        }
    }

    // Clean and limit tags
    tags = tags.map(tag => tag.trim()).filter(tag => tag.length > 0);
    if (tags.length === 0) {
        tags = window.locale === 'ar' ? 
            ['منتج مميز', 'جودة عالية', 'توصيل سريع'] : 
            ['Featured', 'Quality', 'Fast Delivery'];
    }
    tags = tags.slice(0, 3);
    console.log('Tags processed:', tags);

    const tagsHtml = tags.map(tag => `<span class="store-tag">${tag}</span>`).join('');

    // Create button HTML
    const buttonHtml = product.url ? 
        `<a href="${product.url}" target="_blank" style="text-decoration: none;">
            <button class="store-btn">${window.locale === 'ar' ? 'اطلب الآن' : 'Order Now'}</button>
        </a>` :
        `<button class="store-btn" onclick="alert('${window.locale === 'ar' ? 'رابط المنتج غير متوفر حالياً' : 'Product link not available'}')">
            ${window.locale === 'ar' ? 'اطلب الآن' : 'Order Now'}
        </button>`;

    // Build the HTML
    const cardHTML = `
        <div class="store-header">
            <div class="store-logo">
                <img src="${product.photo || ''}" 
                     alt="Product Image" 
                     style="width: 80px; height: 80px; object-fit: cover; border-radius: 15px;"
                     onerror="this.src='https://via.placeholder.com/80x80?text=No+Image';">
            </div>
            <div class="store-info">
                <h3>${product.name}</h3>
                <div class="store-rating">
                    ${starsHtml} ${rating.toFixed(1)} (${product.reviews || 0} ${window.locale === 'ar' ? 'تقييم' : 'reviews'})
                </div>
            </div>
        </div>
        <p class="store-description">${product.description || ''}</p>
        <div class="store-tags">${tagsHtml}</div>
        <div class="store-footer">
            <span class="delivery-time">🚚 ${product.delivery_time || (window.locale === 'ar' ? '30-45 دقيقة' : '30-45 min')}</span>
            ${buttonHtml}
        </div>
    `;
    
    console.log('Card HTML generated, length:', cardHTML.length);
    card.innerHTML = cardHTML;
    console.log('Card innerHTML set');
    
    return card;
}

// Enhanced selectCategory function with debugging
function selectCategory(categoryId, buttonElement) {
    console.log('=== selectCategory called ===');
    console.log('Category ID:', categoryId);
    console.log('Button element:', buttonElement);
    
    // Remove active class from all buttons
    const allButtons = document.querySelectorAll('.category-btn');
    console.log('Found category buttons:', allButtons.length);
    
    allButtons.forEach(btn => {
        btn.classList.remove('active');
        console.log('Removed active class from button');
    });
    
    // Add active class to clicked button
    if (buttonElement) {
        buttonElement.classList.add('active');
        console.log('Added active class to clicked button');
    }
    
    currentCategoryId = categoryId;
    console.log('Current category ID set to:', currentCategoryId);
    
    // Load products
    console.log('Calling loadCategoryProductsViaAPI...');
    loadCategoryProductsViaAPI(categoryId);
}

// Debug function to check page state
function debugPageState() {
    console.log('=== PAGE STATE DEBUG ===');
    console.log('Products container exists:', !!document.getElementById('products-container'));
    console.log('Categories list exists:', !!document.getElementById('categoriesList'));
    console.log('Current category ID:', currentCategoryId);
    console.log('Current city ID:', currentCityId);
    console.log('Categories data:', Object.keys(categoriesData || {}));
    console.log('Window base URL:', window.baseUrl);
    console.log('Window API routes:', window.apiRoutes);
    console.log('Window locale:', window.locale);
    
    const container = document.getElementById('products-container');
    if (container) {
        console.log('Container innerHTML length:', container.innerHTML.length);
        console.log('Container children count:', container.children.length);
        console.log('Container first 200 chars:', container.innerHTML.substring(0, 200));
    }
}

// Call this function in browser console to debug
window.debugPageState = debugPageState;

        function showError(message) {
            const productsContainer = document.getElementById('products-container');
            productsContainer.innerHTML = `
                <div class="empty-state">
                    <h3>${window.locale === 'ar' ? 'خطأ' : 'Error'}</h3>
                    <p>${message}</p>
                </div>
            `;
        }

        // Slider functionality
        function initializeSlider() {
            const slides = document.querySelectorAll('.slide');
            if (slides.length === 0) return;

            startAutoSlide();
            
            // Touch/swipe support
            let startX = 0;
            let startY = 0;
            const sliderTrack = document.getElementById('sliderTrack');
            
            sliderTrack.addEventListener('touchstart', (e) => {
                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;
                stopAutoSlide();
            });
            
            sliderTrack.addEventListener('touchend', (e) => {
                const endX = e.changedTouches[0].clientX;
                const endY = e.changedTouches[0].clientY;
                const diffX = startX - endX;
                const diffY = startY - endY;
                
                if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
                    if (diffX > 0) {
                        changeSlide(1);
                    } else {
                        changeSlide(-1);
                    }
                }
                startAutoSlide();
            });
        }

        function changeSlide(direction) {
            const slides = document.querySelectorAll('.slide');
            const indicators = document.querySelectorAll('.indicator');
            
            if (slides.length === 0) return;
            
            // Remove active class from current indicator
            if (indicators[currentSlideIndex]) {
                indicators[currentSlideIndex].classList.remove('active');
            }
            
            currentSlideIndex += direction;
            
            if (currentSlideIndex >= slides.length) {
                currentSlideIndex = 0;
            } else if (currentSlideIndex < 0) {
                currentSlideIndex = slides.length - 1;
            }
            
            updateSliderPosition();
            
            // Add active class to new indicator
            if (indicators[currentSlideIndex]) {
                indicators[currentSlideIndex].classList.add('active');
            }
        }

        function currentSlide(n) {
            const indicators = document.querySelectorAll('.indicator');
            
            // Remove active class from current indicator
            if (indicators[currentSlideIndex]) {
                indicators[currentSlideIndex].classList.remove('active');
            }
            
            currentSlideIndex = n - 1;
            updateSliderPosition();
            
            // Add active class to new indicator
            if (indicators[currentSlideIndex]) {
                indicators[currentSlideIndex].classList.add('active');
            }
            
            // Restart auto slide
            stopAutoSlide();
            startAutoSlide();
        }

        function updateSliderPosition() {
            const sliderTrack = document.getElementById('sliderTrack');
            const translateX = -currentSlideIndex * 100;
            sliderTrack.style.transform = `translateX(${translateX}%)`;
        }

        function startAutoSlide() {
            stopAutoSlide();
            slideInterval = setInterval(() => {
                changeSlide(1);
            }, 5000);
        }

        function stopAutoSlide() {
            if (slideInterval) {
                clearInterval(slideInterval);
            }
        }

        // Ripple effects
        function attachRippleEffects() {
            // Store card click ripple effect
            document.querySelectorAll('.store-card').forEach(card => {
                // Remove existing listeners
                card.removeEventListener('click', handleRippleClick);
                // Add new listener
                card.addEventListener('click', handleRippleClick);
            });
        }

        function handleRippleClick(e) {
            // Don't trigger on button clicks or links
            if (e.target.classList.contains('store-btn') || 
                e.target.tagName === 'A' || 
                e.target.closest('a') || 
                e.target.closest('.store-btn')) return;
            
            const card = this;
            const ripple = document.createElement('div');
            const rect = card.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: radial-gradient(circle, rgba(255,215,0,0.3) 0%, transparent 70%);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple 0.6s ease-out;
                pointer-events: none;
                z-index: 1;
            `;
            
            card.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        }

        // Enhanced scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animationPlayState = 'running';
                }
            });
        }, observerOptions);

        // Observe all store cards
        document.querySelectorAll('.store-card').forEach(card => {
            observer.observe(card);
        });

        // Smooth category navigation scroll
        const categoriesNav = document.querySelector('.categories-nav');
        let isScrolling = false;

        window.addEventListener('scroll', () => {
            if (!isScrolling) {
                window.requestAnimationFrame(() => {
                    const scrollTop = window.pageYOffset;
                    if (scrollTop > 200) {
                        categoriesNav.style.boxShadow = '0 4px 20px rgba(0,0,0,0.15)';
                    } else {
                        categoriesNav.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
                    }
                    isScrolling = false;
                });
                isScrolling = true;
            }
        });

        // Add CSS for ripple animation and transitions
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(2);
                    opacity: 0;
                }
            }
            
            /* Enhanced store logo styling */
            .store-logo {
                width: 80px !important;
                height: 80px !important;
                border-radius: 15px !important;
                overflow: hidden;
                flex-shrink: 0;
            }
            
            .store-logo img {
                width: 100% !important;
                height: 100% !important;
                object-fit: cover !important;
                border-radius: 15px !important;
            }

            /* Smooth transitions for content updates */
            #products-container {
                transition: opacity 0.3s ease, transform 0.3s ease;
            }

            .store-card {
                animation: storeSlideIn 0.6s ease-out;
                animation-play-state: paused;
                animation-fill-mode: both;
            }

            @keyframes storeSlideIn {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes waveMove {
                0%, 100% { transform: translateX(0px); }
                50% { transform: translateX(-100px); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>