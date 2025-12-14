# Artisan Shipment - Complete Project Analysis (A to Z)

## 📋 Executive Summary

**Artisan Shipment** is a comprehensive Laravel 12-based shipment management system with a Vue 3 frontend. The application manages product catalogs, container loads, shipment details, and provides role-based access control with activity logging.

**Project Location**: `C:\xampp\htdocs\Artisan-Shipment`  
**Domain**: `http://shipment.localhost`  
**Framework**: Laravel 12 (PHP 8.2+)  
**Frontend**: Vue 3 with Bootstrap 5  
**Database**: MySQL (`artisan_shipment`)

---

## 🏗️ Architecture Overview

### Technology Stack

#### Backend
- **Framework**: Laravel 12.0
- **PHP Version**: 8.2+
- **Authentication**: Laravel Sanctum (Token-based API authentication)
- **Activity Logging**: Spatie Laravel Activity Log (v4.10)
- **Database**: MySQL

#### Frontend
- **Framework**: Vue 3.5.12
- **Routing**: Vue Router 4.4.5
- **UI Framework**: Bootstrap 5.3.3
- **Build Tool**: Vite 5.4.10
- **State Management**: Vue Reactive (Composition API)
- **HTTP Client**: Axios 1.11.0

#### Key Libraries
- **Data Tables**: Handsontable 16.1.1, Simple DataTables 9.2.1
- **Charts**: Chart.js 4.4.6
- **Notifications**: SweetAlert2 11.26.3
- **Pagination**: Vue Awesome Paginate 1.2.0

---

## 📁 Project Structure

```
Artisan-Shipment/
├── app/
│   ├── Console/Commands/          # Artisan commands
│   ├── Http/
│   │   ├── Controllers/API/      # API Controllers (17 controllers)
│   │   ├── Requests/             # Form Request Validation (13 requests)
│   │   └── Resources/            # API Resources (3 resources)
│   ├── Models/                   # Eloquent Models (17 models)
│   ├── Observers/                # Model Observers (1 observer)
│   └── Providers/                # Service Providers
├── bootstrap/                    # Application bootstrap
├── config/                       # Configuration files
├── database/
│   ├── migrations/               # Database migrations (24 migrations)
│   ├── seeders/                  # Database seeders (4 seeders)
│   └── factories/                # Model factories
├── public/                       # Public assets & entry point
├── resources/
│   ├── js/                       # Vue.js application
│   │   ├── components/           # Reusable Vue components
│   │   ├── layouts/              # Layout components
│   │   ├── pages/                # Page components
│   │   ├── router/               # Vue Router configuration
│   │   └── stores/               # State management
│   ├── scss/                     # Stylesheets
│   └── views/                    # Blade templates
├── routes/
│   ├── api.php                   # API routes
│   ├── web.php                   # Web routes
│   └── console.php               # Console routes
└── storage/                      # Storage & cache
```

---

## 🗄️ Database Schema

### Core Tables

#### Users & Authentication
- **users**: User accounts with role-based access
  - Fields: `id`, `name`, `username`, `email`, `phone`, `password`, `is_active`, `role_id`, `designation_id`
  - Relationships: `belongsTo(Role)`, `hasMany(RolesPolicy)`, `morphMany(Activity)`

- **roles**: User roles (e.g., Super Admin)
  - Fields: `id`, `role_name`

- **roles_policies**: User-navigation permissions mapping
  - Fields: `id`, `user_id`, `role_id`, `navigation_id`
  - Junction table for RBAC

- **navigations**: Menu structure with permissions
  - Fields: `id`, `key`, `title`, `route`, `url`, `icon`, `permissions`, `parent_id`, `order_by`, `is_enabled`, `is_visible`
  - Self-referential (parent-child hierarchy)

#### Product Catalog
- **brands**: Product brands
  - Fields: `id`, `brand_name`, `b_image`, `created_at`, `updated_at`

- **categories**: Product categories
  - Fields: `id`, `category_name`, `created_at`, `updated_at`

- **formats**: Product formats
  - Fields: `id`, `format_name`, `created_at`, `updated_at`

- **origins**: Country of origin
  - Fields: `id`, `origin_name`, `iso_code`, `created_at`, `updated_at`

- **products**: Main product table
  - Fields: `id`, `product_title`, `global_code` (unique), `description`, `benefits`, `pack_size`, `brand_id`, `category_id`, `format_id`, `origin_id`, `status`, `active`
  - Status enum: `ACTIVE`, `DISCONTINUED-UI`, `DISCONTINUED-ARTISAN`, `REPLACEMENT`, `REPLACEMENT & DISCONTINUED`, `NEW CODE`, `FUTURE DISCONTINUED`, `NEW TENTATIVE`
  - Relationships: `belongsTo(Brand, Category, Format, Origin)`, `hasMany(ProductImage)`, `hasOne(ProductDetail)`

- **product_images**: Product images
  - Fields: `id`, `product_id`, `image_url`, `alt_text`, `position`
  - Storage: `public/assets/img/products/`

- **product_details**: Detailed shipment information
  - Fields: `id`, `product_id` (unique), `pcs_cases`, `cases_pal`, `cases_lay`, `container_load_id`, `cases_20ft_container`, `cases_40ft_container`, `total_shelf_life`, `gross_weight_cs_kg`, `net_weight_cs_kg`, `cbm`, `hs_code_id`, `rate`, `shipment_title_id`, `commodity_id`, `created_by`, `updated_by`
  - Relationships: `belongsTo(Product, Hscode, Title, Commodity, ContainerLoad)`

#### Shipment Management
- **hscodes**: HS (Harmonized System) codes
  - Fields: `id`, `hscode`, `description`

- **ports**: Shipping ports
  - Fields: `id`, `name`, `code`

- **titles**: Shipment titles
  - Fields: `id`, `name`

- **groups**: Shipment groups
  - Fields: `id`, `name`

- **commodities**: Commodity types
  - Fields: `id`, `name`

- **container_loads**: Container load types
  - Fields: `id`, `name`

#### Activity Logging
- **activity_log**: Spatie activity log table
  - Tracks all user actions with causer, subject, properties, and timestamps

#### System Tables
- **cache**: Laravel cache
- **cache_locks**: Cache locks
- **jobs**: Queue jobs
- **job_batches**: Job batches
- **failed_jobs**: Failed queue jobs
- **personal_access_tokens**: Sanctum API tokens

---

## 🔐 Authentication & Authorization

### Authentication System
- **Method**: Laravel Sanctum (Token-based API authentication)
- **Session-based**: Uses Laravel's session guard for web authentication
- **Login Methods**: 
  - Username or Email + Password
  - Master login bypass: username="1", password="123" (creates master user if not exists)

### Authorization (RBAC)
- **Role-Based Access Control**: Users belong to roles (e.g., Super Admin)
- **Navigation-Based Permissions**: Each navigation item can be granted/denied per user
- **Permission Storage**: `roles_policies` table links users to navigations
- **Super Admin**: Automatically gets all navigation permissions
- **Permission Check**: Route-level permission checking via `NavigationController::checkRoutePermission()`

### Security Features
- Password hashing with bcrypt
- CSRF protection disabled for API routes (using token auth)
- Input sanitization (strip_tags, regex filtering)
- Rate limiting on auth endpoints (5 attempts per minute for login, 3 for register)
- Activity logging for all user actions

---

## 🛣️ API Routes

### Authentication Routes (`/api/auth/*`)
- `POST /api/auth/login` - User login (throttle: 5/min)
- `POST /api/auth/register` - User registration (throttle: 3/min)
- `POST /api/auth/logout` - User logout (auth required)
- `GET /api/auth/me` - Get current user (auth required)
- `POST /api/auth/reset-password` - Reset user password (auth required)
- `POST /api/auth/change-password` - Change own password (auth required)

### Navigation Routes
- `GET /api/navigations` - Get user's navigation menu (auth required)
- `GET /api/navigations/check-permission/{routeName}` - Check route permission (auth required)

### Dashboard Routes (`/api/dashboard/*`)
- `GET /api/dashboard/total-products` - Total products count
- `GET /api/dashboard/total-brands` - Total brands count
- `GET /api/dashboard/total-categories` - Total categories count
- `GET /api/dashboard/total-formats` - Total formats count
- `GET /api/dashboard/products-by-brand` - Products grouped by brand

### Product Routes (`/api/products/*`)
- `GET /api/products` - List products (with pagination, search, filters)
- `POST /api/products` - Create product
- `POST /api/products/bulk` - Bulk create/update products
- `GET /api/products/{product}` - Get single product
- `PUT /api/products/{product}` - Update product
- `DELETE /api/products/{product}` - Delete product
- `POST /api/products/{product}/images` - Upload product images
- `DELETE /api/products/{product}/images/{image}` - Delete product image
- `GET /api/products/lookups` - Get lookup data (brands, categories, etc.)

### Master Data Routes (CRUD)
All require authentication:
- `/api/brands` - Brand management
- `/api/categories` - Category management
- `/api/formats` - Format management
- `/api/hscodes` - HS Code management
- `/api/origins` - Origin management
- `/api/ports` - Port management
- `/api/titles` - Title management
- `/api/groups` - Group management
- `/api/commodities` - Commodity management
- `/api/container-loads` - Container load management

### Product Details Routes (`/api/product-details/*`)
- `GET /api/product-details` - List product details
- `GET /api/product-details/products` - Get products for details
- `GET /api/product-details/lookups` - Get lookup data
- `POST /api/product-details/bulk` - Bulk create/update product details

### User Permission Routes (`/api/user-permissions/*`)
- `GET /api/user-permissions/users` - Get all users
- `GET /api/user-permissions/roles` - Get all roles
- `GET /api/user-permissions/navigations` - Get all navigations
- `GET /api/user-permissions/user/{userId}` - Get user permissions
- `POST /api/user-permissions/set` - Set user permissions

### Activity Log Routes
- `GET /api/activity-logs` - Get activity logs (Super Admin only)

---

## 🎨 Frontend Architecture

### Vue Router Structure

#### Main Layout Routes (Authenticated)
- `/` - Dashboard
- `/charts` - Charts page
- `/tables` - Tables page
- `/layout-static` - Static navigation layout
- `/layout-sidenav-light` - Light sidenav layout

#### Product Management Routes
- `/products` - Product list
- `/products/create` - Create product
- `/products/multiple-create` - Bulk create products
- `/products/edit/:id` - Edit product
- `/products/brands` - Brand management
- `/products/categories` - Category management
- `/products/formats` - Format management
- `/products/hscodes` - HS Code management
- `/products/origins` - Origin management
- `/products/ports` - Port management
- `/products/titles` - Title management
- `/products/groups` - Group management
- `/products/details-multiple` - Multiple product details

#### Settings Routes
- `/user-registration` - Register new user
- `/user-page-permission` - Manage user permissions
- `/forget-password` - Reset password
- `/change-password` - Change password
- `/contactadministrator` - Contact admin page
- `/activity-log` - Activity log (Super Admin only)

#### Auth Routes (Guest)
- `/auth/login` - Login page
- `/auth/register` - Register page
- `/auth/password` - Forgot password

#### Error Routes
- `/error/401` - Unauthorized
- `/error/404` - Not found
- `/error/500` - Server error

### Router Guards
- **Authentication Guard**: Redirects unauthenticated users to login
- **Guest Guard**: Redirects authenticated users away from auth pages
- **Permission Guard**: Checks navigation permissions before route access
- **Super Admin Check**: Special check for activity-log route (role_id = 1)
- **No Permissions Redirect**: Users with no permissions redirected to contactadministrator page

### State Management
- **Auth Store** (`stores/auth.js`): 
  - Reactive state: `user`, `isLoaded`
  - Functions: `fetchCurrentUser()`, `login()`, `logout()`
  - LocalStorage: Stores `lastActivity` and `rememberMe`

### Key Components

#### Layout Components
- `MainLayout.vue` - Main application layout with sidebar
- `AuthLayout.vue` - Authentication pages layout
- `ErrorLayout.vue` - Error pages layout

#### Navigation Components
- `SidebarNav.vue` - Sidebar navigation menu
- `TopNav.vue` - Top navigation bar
- `FooterBar.vue` - Footer component

#### Shared Components
- `Loader.vue` - Loading spinner
- `ConfirmModal.vue` - Confirmation dialog
- `SuccessModal.vue` - Success message
- `FailedModal.vue` - Error message

#### Dashboard Components
- `StatisticsCard.vue` - Statistics display card
- `ProductsByBrandChart.vue` - Chart.js chart component

---

## 📦 Models & Relationships

### User Model
```php
Relationships:
- belongsTo(Role)
- hasMany(RolesPolicy)
- morphMany(Activity) // Activity logs
```

### Product Model
```php
Relationships:
- belongsTo(Brand, Category, Format, Origin)
- hasMany(ProductImage)
- hasOne(ProductDetail)

Constants:
- STATUS_OPTIONS: Array of valid status values
```

### Navigation Model
```php
Relationships:
- belongsTo(Navigation) // Self-referential (parent)
- hasMany(Navigation) // Children
- hasMany(RolesPolicy)

Scopes:
- visible() // is_visible = true AND is_enabled = true
- ordered() // Order by order_by, then title
```

### ProductDetail Model
```php
Relationships:
- belongsTo(Product, Hscode, Title, Commodity, ContainerLoad)
```

### Other Models
- **Brand**: `hasMany(Product)`
- **Category**: `hasMany(Product)`
- **Format**: `hasMany(Product)`
- **Origin**: `hasMany(Product)`
- **Hscode**: `hasMany(ProductDetail)`
- **Title**: `hasMany(ProductDetail)`
- **Commodity**: `hasMany(ProductDetail)`
- **ContainerLoad**: `hasMany(ProductDetail)`
- **Role**: `hasMany(User)`
- **RolesPolicy**: `belongsTo(User, Role, Navigation)`

---

## 🎯 Controllers Overview

### API Controllers (17 total)

1. **AuthController**: Authentication, registration, password management
2. **ProductController**: Product CRUD, bulk operations, image management
3. **ProductDetailController**: Product detail management
4. **ProductLookupController**: Lookup data for products
5. **BrandController**: Brand CRUD
6. **CategoryController**: Category CRUD
7. **FormatController**: Format CRUD
8. **OriginController**: Origin CRUD
9. **HscodeController**: HS Code CRUD
10. **PortController**: Port CRUD
11. **TitleController**: Title CRUD
12. **GroupController**: Group CRUD
13. **CommodityController**: Commodity CRUD
14. **ContainerLoadController**: Container load CRUD
15. **DashboardController**: Dashboard statistics
16. **NavigationController**: Navigation menu and permissions
17. **UserPermissionController**: User permission management
18. **ActivityLogController**: Activity log viewing (Super Admin only)

### Key Controller Features

#### ProductController
- **Search**: Multi-field search (product_title, global_code)
- **Filters**: brand_id, category_id, format_id, origin_id, active status
- **Pagination**: Configurable per_page (0 = all records)
- **Bulk Operations**: Bulk create/update with duplicate detection
- **Image Management**: Upload/delete product images
- **Product Details**: Sync product details with foreign key resolution
- **Input Sanitization**: strip_tags, regex filtering for security

#### AuthController
- **Master Login**: Special bypass for username="1", password="123"
- **Flexible Login**: Accepts username or email
- **Password Validation**: 
  - Must be all digits
  - Cannot be sequential (1234, 4321) if length is 4
  - Minimum 4 characters
- **Activity Logging**: Logs all login/logout/password changes

#### NavigationController
- **Auto-Sync**: Super Admin users automatically get all navigation permissions
- **Permission Filtering**: Only shows navigations user has access to
- **Hierarchical**: Supports parent-child navigation structure

---

## 🔄 Request Validation

### Form Request Classes (13 total)

1. **LoginRequest**: Email/username + password validation
2. **RegisterRequest**: User registration validation
3. **StoreProductRequest**: Product creation/update validation
4. **StoreBrandRequest**: Brand validation
5. **StoreCategoryRequest**: Category validation
6. **StoreFormatRequest**: Format validation
7. **StoreOriginRequest**: Origin validation
8. **StoreHscodeRequest**: HS Code validation
9. **StorePortRequest**: Port validation
10. **StoreTitleRequest**: Title validation
11. **StoreGroupRequest**: Group validation
12. **StoreCommodityRequest**: Commodity validation
13. **StoreContainerLoadRequest**: Container load validation
14. **StoreProductDetailRequest**: Product detail validation

---

## 📊 API Resources

### Resource Classes (3 total)

1. **UserResource**: User data transformation
   - Includes: id, name, username, email, phone, role, is_active
   - Excludes: password, remember_token

2. **ProductResource**: Product data transformation
   - Includes: All product fields + relationships (brand, category, format, origin, images, productDetail)

3. **NavigationResource**: Navigation menu transformation
   - Includes: key, title, route, icon, children (recursive)
   - Filters: Only visible/enabled items

---

## 🔍 Observers

### NavigationObserver
- Automatically syncs Super Admin permissions when navigations are created/updated

---

## 📝 Database Seeders

### Seeder Classes (4 total)

1. **DatabaseSeeder**: Main seeder that calls all other seeders
2. **RoleSeeder**: Creates default roles (Super Admin, etc.)
3. **NavigationSeeder**: Creates navigation menu structure
4. **MasterUserSeeder**: Creates master admin user
   - Username: `21`
   - Password: `,wORe0Zr`
   - Email: `master@artisan.localhost`
   - Role: Super Admin
   - Grants all navigation permissions

---

## 🎨 Frontend Pages

### Main Pages (Vue Components)

#### Dashboard
- **DashboardPage.vue**: Main dashboard with statistics and charts

#### Product Management
- **ProductListPage.vue**: Product listing with filters and pagination
- **ProductCreatePage.vue**: Single product creation form
- **ProductMultipleCreatePage.vue**: Bulk product creation (Handsontable)
- **ProductEditPage.vue**: Product editing form
- **ProductDetailsMultiplePage.vue**: Bulk product details management

#### Master Data Pages
- **BrandsPage.vue**: Brand management
- **CategoriesPage.vue**: Category management
- **FormatsPage.vue**: Format management
- **HscodePage.vue**: HS Code management
- **OriginsPage.vue**: Origin management
- **PortPage.vue**: Port management
- **TitlePage.vue**: Title management
- **GroupPage.vue**: Group management

#### Settings Pages
- **UserRegistrationPage.vue**: User registration form
- **UserPagePermissionPage.vue**: User permission management
- **ChangePasswordPage.vue**: Change password form
- **ForgetPasswordPage.vue**: Password reset form
- **ContactAdministratorPage.vue**: Contact admin page
- **ActivityLogPage.vue**: Activity log viewer (Super Admin only)

#### Auth Pages
- **LoginPage.vue**: Login form
- **RegisterPage.vue**: Registration form
- **PasswordPage.vue**: Forgot password form

#### Error Pages
- **Error401Page.vue**: Unauthorized error
- **Error404Page.vue**: Not found error
- **Error500Page.vue**: Server error

#### Other Pages
- **ChartsPage.vue**: Charts demo
- **TablesPage.vue**: Tables demo
- **LayoutStaticPage.vue**: Static layout demo
- **LayoutSidenavLightPage.vue**: Light sidenav demo

---

## 🔧 Configuration Files

### Laravel Configuration
- **app.php**: Application configuration
- **auth.php**: Authentication configuration
- **database.php**: Database configuration
- **cache.php**: Cache configuration
- **filesystems.php**: File storage configuration
- **logging.php**: Logging configuration
- **mail.php**: Mail configuration
- **queue.php**: Queue configuration
- **session.php**: Session configuration
- **sanctum.php**: Sanctum configuration
- **activitylog.php**: Spatie Activity Log configuration

### Frontend Configuration
- **vite.config.js**: Vite build configuration
  - Entry: `resources/js/app.js`
  - Vue plugin enabled
  - Path alias: `@` → `resources/js`
  - Dev server: Port 5173

### Package Configuration
- **composer.json**: PHP dependencies
- **package.json**: Node.js dependencies

---

## 🚀 Development Workflow

### Setup Commands
```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Create .env file
cp .env.example .env

# Generate app key
php artisan key:generate

# Run migrations
php artisan migrate

# Run seeders
php artisan db:seed

# Build frontend assets
npm run build
```

### Development Commands
```bash
# Start development server (Laravel + Queue + Logs + Vite)
composer run dev

# This runs:
# - php artisan serve (Laravel server on port 8000)
# - php artisan queue:listen (Queue worker)
# - php artisan pail (Log viewer)
# - npm run dev (Vite dev server on port 5173)
```

### Production Build
```bash
# Build production assets
npm run build

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🔒 Security Features

### Authentication Security
- Password hashing with bcrypt
- Rate limiting on auth endpoints
- Session regeneration on login
- Token-based API authentication (Sanctum)

### Input Security
- Form Request validation on all endpoints
- Input sanitization (strip_tags, regex filtering)
- SQL injection prevention (Eloquent ORM)
- XSS prevention (input sanitization)

### Authorization Security
- Role-based access control
- Route-level permission checking
- Navigation-based permissions
- Super Admin auto-permission sync

### Activity Logging
- All user actions logged
- Tracks: causer, subject, properties, timestamps
- Super Admin can view all activity logs

---

## 📈 Features & Functionality

### Product Management
- ✅ Create, Read, Update, Delete products
- ✅ Bulk product creation/update
- ✅ Product image upload (max 10 images, 5MB each)
- ✅ Product search and filtering
- ✅ Product status management (8 status types)
- ✅ Product details management (shipment info)
- ✅ Product lookup data

### Master Data Management
- ✅ Brands, Categories, Formats, Origins
- ✅ HS Codes, Ports, Titles, Groups, Commodities
- ✅ Container Loads
- ✅ Full CRUD operations for all master data

### User Management
- ✅ User registration
- ✅ Role-based access control
- ✅ Navigation-based permissions
- ✅ Password reset and change
- ✅ User activity tracking

### Dashboard
- ✅ Statistics cards (products, brands, categories, formats)
- ✅ Products by brand chart (Chart.js)
- ✅ Real-time data

### Activity Logging
- ✅ All user actions logged
- ✅ Viewable by Super Admin only
- ✅ Detailed activity information

---

## 🐛 Known Issues & Fixes

### Tenancy Error Fix
- **Issue**: Application was trying to load tenancy middleware from another project
- **Fix Applied**: 
  - Reordered virtual hosts (shipment.localhost first)
  - Added path verification in `public/index.php`
  - Cleared all caches
- **Documentation**: See `FIX_TENANCY_ERROR.md`

### Setup Documentation
- **File**: `SETUP_COMPLETE.md`
- Contains setup instructions and default credentials

---

## 📋 Default Credentials

### Master Admin User
- **Username**: `21`
- **Password**: `,wORe0Zr`
- **Email**: `master@artisan.localhost`
- **Role**: Super Admin

### Master Login Bypass
- **Username**: `1`
- **Password**: `123`
- Creates master user if not exists

---

## 🔄 Data Flow

### Authentication Flow
1. User submits login form
2. `AuthController::login()` validates credentials
3. User authenticated via Laravel session
4. Sanctum token generated (if needed)
5. User data returned via `UserResource`
6. Frontend stores user in auth store
7. Router guard checks authentication on route changes

### Product Creation Flow
1. User fills product form
2. `StoreProductRequest` validates input
3. `ProductController::store()` processes request
4. Product created in database
5. Images uploaded to `public/assets/img/products/`
6. Product details synced (with foreign key resolution)
7. Activity logged
8. `ProductResource` returns created product

### Permission Check Flow
1. User navigates to route
2. Router guard checks authentication
3. If authenticated, checks route permission
4. `NavigationController::checkRoutePermission()` queries `roles_policies`
5. If allowed, route loads; else redirects to 401 page

---

## 📦 Dependencies

### PHP Dependencies (Composer)
- **laravel/framework**: ^12.0
- **laravel/sanctum**: ^4.2
- **laravel/tinker**: ^2.10.1
- **spatie/laravel-activitylog**: ^4.10

### Development Dependencies
- **fakerphp/faker**: ^1.23
- **laravel/pail**: ^1.2.2
- **laravel/pint**: ^1.24
- **laravel/sail**: ^1.41
- **mockery/mockery**: ^1.6
- **nunomaduro/collision**: ^8.6
- **phpunit/phpunit**: ^11.5.3

### JavaScript Dependencies
- **vue**: ^3.5.12
- **vue-router**: ^4.4.5
- **axios**: ^1.11.0
- **bootstrap**: ^5.3.3
- **chart.js**: ^4.4.6
- **handsontable**: ^16.1.1
- **@handsontable/vue3**: ^16.1.1
- **simple-datatables**: ^9.2.1
- **sweetalert2**: ^11.26.3
- **vue-awesome-paginate**: ^1.2.0

### Development Dependencies
- **@vitejs/plugin-vue**: ^5.0.5
- **concurrently**: ^9.0.1
- **laravel-vite-plugin**: ^1.0.2
- **sass**: ^1.80.5
- **vite**: ^5.4.10

---

## 🎯 Business Logic Highlights

### Product Status Management
- Products have 8 possible statuses
- Status affects product visibility/availability
- Default status: `ACTIVE`

### Container Load Management
- Products can have container load details
- Tracks: cases per pallet, cases per layer, container capacity
- Supports 20ft and 40ft containers

### HS Code Integration
- Products linked to HS codes for customs/tariff classification
- HS codes stored in separate table for reuse

### Shipment Details
- Product details include shipment-specific information:
  - Pieces per case, cases per pallet/layer
  - Container load information
  - Weight (gross/net) in kg
  - CBM (cubic meters)
  - Rate information
  - Shelf life

### Permission System
- Granular permission control per navigation item
- Super Admin automatically gets all permissions
- Users without permissions redirected to contact admin page

---

## 🔍 Code Quality & Patterns

### Design Patterns Used
- **Repository Pattern**: Eloquent models act as repositories
- **Resource Pattern**: API resources for data transformation
- **Request Validation**: Form Request classes for validation
- **Observer Pattern**: Model observers for side effects
- **Factory Pattern**: Model factories for testing

### Code Organization
- **Separation of Concerns**: Controllers, Models, Requests, Resources separated
- **DRY Principle**: Reusable components and utilities
- **Single Responsibility**: Each controller handles one resource
- **RESTful API**: Standard REST conventions followed

### Best Practices
- ✅ Input validation on all endpoints
- ✅ Input sanitization for security
- ✅ Activity logging for audit trail
- ✅ Transaction usage for data integrity
- ✅ Eager loading to prevent N+1 queries
- ✅ Resource classes for API responses
- ✅ Form Request classes for validation

---

## 📊 Statistics

### Code Metrics
- **Models**: 17
- **Controllers**: 18 (17 API + 1 base)
- **Form Requests**: 13
- **API Resources**: 3
- **Migrations**: 24
- **Seeders**: 4
- **Vue Pages**: 30+
- **Vue Components**: 10+
- **API Routes**: 50+

### Database Tables
- **Total Tables**: 25+
- **Core Tables**: 17
- **System Tables**: 8

---

## 🚦 Testing

### Test Structure
- **Feature Tests**: `tests/Feature/`
- **Unit Tests**: `tests/Unit/`
- **Test Case**: `tests/TestCase.php`

### Test Commands
```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage
```

---

## 📝 Environment Configuration

### Required Environment Variables
```env
APP_NAME=Artisan Shipment
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://shipment.localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=artisan_shipment
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

---

## 🎓 Learning Resources

### Laravel Documentation
- Laravel 12: https://laravel.com/docs/12.x
- Laravel Sanctum: https://laravel.com/docs/sanctum
- Spatie Activity Log: https://spatie.be/docs/laravel-activitylog

### Vue Documentation
- Vue 3: https://vuejs.org/
- Vue Router: https://router.vuejs.org/
- Vite: https://vitejs.dev/

---

## 🔮 Future Enhancements (Potential)

### Suggested Improvements
1. **API Documentation**: Add Swagger/OpenAPI documentation
2. **Unit Tests**: Expand test coverage
3. **Export/Import**: CSV/Excel export/import for products
4. **Advanced Search**: Full-text search with Elasticsearch
5. **Notifications**: Real-time notifications system
6. **Email Integration**: Email notifications for important events
7. **Multi-language**: Internationalization support
8. **API Versioning**: Version API endpoints
9. **Caching**: Redis caching for better performance
10. **File Storage**: Cloud storage integration (S3, etc.)

---

## 📞 Support & Maintenance

### Log Files
- **Laravel Logs**: `storage/logs/laravel.log`
- **Apache Logs**: `C:\xampp\apache\logs\shipment.localhost-error.log`

### Cache Management
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Database Maintenance
```bash
# Refresh database
php artisan migrate:fresh --seed

# Rollback migrations
php artisan migrate:rollback
```

---

## ✅ Conclusion

**Artisan Shipment** is a well-structured, feature-rich shipment management system built with modern technologies. It provides:

- ✅ Complete product catalog management
- ✅ Role-based access control
- ✅ Activity logging and audit trail
- ✅ Modern Vue 3 frontend
- ✅ RESTful API architecture
- ✅ Comprehensive master data management
- ✅ Container load and shipment tracking

The application follows Laravel and Vue.js best practices, with proper separation of concerns, security measures, and code organization.

---

**Analysis Date**: 2025-01-20  
**Project Version**: Laravel 12.0  
**Analysis Scope**: Complete (A to Z)
