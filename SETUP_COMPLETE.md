# Artisan Shipment - Setup Complete ✅

## Project Analysis Summary

This is a **Laravel 12** shipment management system with a **Vue 3** frontend. The application includes:

### Key Features:
- **Product Management**: Products, Brands, Categories, Formats, Origins, HS Codes, Ports, Titles, Groups, Commodities
- **Container Load Management**: Track container loads and product details
- **User Management**: Role-based access control with permissions
- **Activity Logging**: Track user activities using Spatie Activity Log
- **Dashboard**: Statistics and charts for products by brand
- **Authentication**: Laravel Sanctum for API authentication

### Technology Stack:
- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: Vue 3, Bootstrap 5, Vite
- **Database**: MySQL
- **Additional Packages**: 
  - Laravel Sanctum (API authentication)
  - Spatie Activity Log
  - Handsontable (data tables)
  - Chart.js (charts)
  - SweetAlert2 (notifications)

## Setup Steps Completed

✅ **1. Environment Configuration**
   - Created `.env` file from `.env.example`
   - Configured `APP_URL=http://shipment.localhost`
   - Generated application key
   - Database configured for MySQL (artisan_shipment)

✅ **2. Dependencies Installed**
   - PHP dependencies via Composer
   - Node.js dependencies via npm (177 packages)

✅ **3. Database Setup**
   - Created database: `artisan_shipment`
   - All migrations executed (24 migrations)
   - Seeders executed:
     - RoleSeeder (creates roles)
     - NavigationSeeder (creates navigation menu)
     - MasterUserSeeder (creates master admin user)

✅ **4. Virtual Host Configuration**
   - Added virtual host entry in `C:\xampp\apache\conf\extra\httpd-vhosts.conf`
   - DocumentRoot: `C:/xampp/htdocs/Artisan-Shipment/public`

✅ **5. Frontend Assets**
   - Built production assets using Vite
   - Assets available in `public/build/`

✅ **6. Storage & Cache**
   - Storage directories created
   - Storage symlink created
   - Cache cleared

## ⚠️ Manual Steps Required

### 1. Add Hosts File Entry (Requires Administrator)
You need to add the following entry to your Windows hosts file:

**File Location**: `C:\Windows\System32\drivers\etc\hosts`

**Add these lines**:
```
127.0.0.1    shipment.localhost
127.0.0.1    www.shipment.localhost
```

**How to do it**:
1. Open Notepad as Administrator (Right-click → Run as administrator)
2. Open file: `C:\Windows\System32\drivers\etc\hosts`
3. Add the lines above at the end of the file
4. Save the file

### 2. Restart Apache
1. Open XAMPP Control Panel
2. Stop Apache (if running)
3. Start Apache again

### 3. Verify Database Password
The `.env` file is configured with:
- `DB_USERNAME=root`
- `DB_PASSWORD=` (empty)

If your MySQL root password is different, update the `.env` file accordingly.

## Access Information

### Application URL
- **Main URL**: http://shipment.localhost
- **Alternative**: http://www.shipment.localhost

### Default Admin Credentials
Based on the MasterUserSeeder:
- **Username**: `21`
- **Password**: `,wORe0Zr`
- **Email**: master@artisan.localhost
- **Role**: Super Admin

## Project Structure

```
Artisan-Shipment/
├── app/
│   ├── Http/Controllers/API/    # API Controllers
│   ├── Models/                  # Eloquent Models
│   └── Observers/               # Model Observers
├── database/
│   ├── migrations/              # Database migrations
│   └── seeders/                 # Database seeders
├── resources/
│   ├── js/                      # Vue.js components
│   ├── scss/                    # Stylesheets
│   └── views/                   # Blade templates
├── routes/
│   ├── api.php                  # API routes
│   └── web.php                  # Web routes
└── public/                      # Public assets
```

## API Endpoints

The application uses Laravel Sanctum for authentication. Main API endpoints include:

- `/api/auth/login` - User login
- `/api/auth/register` - User registration
- `/api/auth/me` - Get current user (authenticated)
- `/api/products` - Product CRUD operations
- `/api/dashboard/*` - Dashboard statistics
- `/api/navigations` - Navigation menu
- And many more...

## Development Commands

### Start Development Server
```bash
composer run dev
```
This starts:
- Laravel server (port 8000)
- Queue worker
- Log viewer (Pail)
- Vite dev server (port 5173)

### Build Assets
```bash
npm run build
```

### Run Migrations
```bash
php artisan migrate
```

### Run Seeders
```bash
php artisan db:seed
```

## Troubleshooting

### If the site doesn't load:
1. Check if Apache is running in XAMPP
2. Verify hosts file entry is correct
3. Check Apache error logs: `C:\xampp\apache\logs\shipment.localhost-error.log`
4. Verify virtual host configuration

### Database Connection Issues:
1. Ensure MySQL is running in XAMPP
2. Verify database credentials in `.env`
3. Check if database `artisan_shipment` exists

### Permission Issues:
- On Windows, file permissions are usually not an issue
- If you encounter permission errors, ensure the web server user has read/write access to:
  - `storage/` directory
  - `bootstrap/cache/` directory

## Next Steps

1. ✅ Add hosts file entry (see Manual Steps above)
2. ✅ Restart Apache
3. ✅ Access http://shipment.localhost
4. ✅ Login with default admin credentials
5. Start using the application!

---

**Setup completed on**: $(Get-Date)
**Project Location**: C:\xampp\htdocs\Artisan-Shipment

