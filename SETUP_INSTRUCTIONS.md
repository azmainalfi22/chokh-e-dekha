# Chokh-e-Dekha - Setup Instructions

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- Composer
- Node.js & NPM
- MySQL/MariaDB or SQLite
- Google Maps API Key (for map features)

---

## 📦 Installation

### 1. **Clone & Install Dependencies**
```bash
# Navigate to project
cd chokh-e-dekha

# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### 2. **Environment Configuration**
Create `.env` file (if not exists):
```bash
cp .env.example .env  # Linux/Mac
copy .env.example .env  # Windows
```

Edit `.env` and set these values:
```env
APP_NAME="Chokh-e-Dekha"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Dhaka
APP_LOCALE=bn
APP_FALLBACK_LOCALE=en

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=chokh_e_dekha
DB_USERNAME=root
DB_PASSWORD=

# Google Maps (Required for Command Center)
GOOGLE_MAPS_API_KEY=your_google_maps_api_key_here

# Mail (Optional - for notifications)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="no-reply@chokhe-dekha.gov.bd"
MAIL_FROM_NAME="${APP_NAME}"
```

### 3. **Generate Application Key**
```bash
php artisan key:generate
```

### 4. **Run Migrations**
```bash
php artisan migrate --seed
```

### 5. **Build Frontend Assets**
```bash
npm run build
# Or for development with hot reload:
npm run dev
```

### 6. **Clear Cache**
```bash
php artisan optimize:clear
```

### 7. **Start Server**
```bash
php artisan serve
```

Visit: **http://localhost:8000**

---

## 🗺️ **Google Maps Setup** (Required for Command Center)

### Step 1: Get API Key
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project (or select existing)
3. Enable these APIs:
   - **Maps JavaScript API**
   - **Geocoding API** (optional, for address lookup)
4. Create credentials → API Key
5. Copy the API key

### Step 2: Restrict API Key (Production)
For security, restrict your API key:
- **Application restrictions**: HTTP referrers
  - Add: `http://localhost:8000/*`
  - Add: `https://yourdomain.gov.bd/*`
- **API restrictions**: 
  - Select: Maps JavaScript API
  - Select: Geocoding API

### Step 3: Add to .env
```env
GOOGLE_MAPS_API_KEY=AIzaSyXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
```

### Step 4: Test
Visit: **http://localhost:8000/admin/command-center**

---

## 🏛️ **Access Points**

### Public Pages (No Login Required)
- **Homepage**: http://localhost:8000/welcome
- **Transparency Board**: http://localhost:8000/transparency
- **Open Data API**: http://localhost:8000/api/open-data
- **All Reports**: http://localhost:8000/reports

### Citizen Portal (Login Required)
- **Dashboard**: http://localhost:8000/dashboard
- **My Reports**: http://localhost:8000/reports
- **Create Report**: http://localhost:8000/reports/create
- **RTI Request**: http://localhost:8000/legal/rti
- **Profile**: http://localhost:8000/profile

### Admin/Government Portal (Admin Login)
- **Admin Dashboard**: http://localhost:8000/admin/dashboard
- **Command Center** ⭐: http://localhost:8000/admin/command-center
- **Manage Reports**: http://localhost:8000/admin/reports
- **Map View**: http://localhost:8000/admin/reports/map
- **Users Management**: http://localhost:8000/admin/users

---

## 👤 **Default Users**

If you ran migrations with `--seed`, you'll have:

**Admin Account:**
```
Email: admin@example.com
Password: password
```

**Citizen Account:**
```
Email: user@example.com
Password: password
```

⚠️ **Change these in production!**

---

## 📱 **Mobile Testing**

Test on mobile devices:
```bash
# Find your local IP
ipconfig  # Windows
ifconfig  # Linux/Mac

# Start server on all interfaces
php artisan serve --host=0.0.0.0 --port=8000
```

Access from phone: `http://YOUR_IP:8000`

---

## 🎨 **Theme Customization**

Edit `resources/css/theme.css` to change:
- Colors (BD green, red, navy)
- Typography (Bengali/English fonts)
- Spacing, shadows, radius
- Status badge colors

Then rebuild:
```bash
npm run build
```

---

## 🔐 **Security Checklist (Production)**

Before deploying to production:

### 1. Environment
```env
APP_ENV=production
APP_DEBUG=false
```

### 2. HTTPS
- Install SSL certificate (Let's Encrypt recommended)
- Force HTTPS in middleware (already configured)

### 3. Database
- Use strong passwords
- Disable remote access if not needed

### 4. Permissions
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache  # Linux
```

### 5. API Keys
- Restrict Google Maps API key to your domain
- Don't commit `.env` to git

### 6. Update Dependencies
```bash
composer update
npm update
```

---

## 🗄️ **Database Backup**

Regular backups:
```bash
# MySQL
mysqldump -u root -p chokh_e_dekha > backup_$(date +%Y%m%d).sql

# Restore
mysql -u root -p chokh_e_dekha < backup_20251024.sql
```

---

## 🐛 **Troubleshooting**

### Problem: "Class 'App\Http\Middleware\SecurityHeaders' not found"
**Solution:**
```bash
composer dump-autoload
php artisan optimize:clear
```

### Problem: Google Maps not loading
**Solutions:**
1. Check API key in `.env`
2. Verify API is enabled in Google Cloud
3. Check browser console for errors
4. Ensure billing is enabled (Google requires it)

### Problem: CSS not applying
**Solution:**
```bash
npm run build
php artisan optimize:clear
```

### Problem: "Target class [NotificationService] does not exist"
**Solution:**
```bash
composer dump-autoload
php artisan config:clear
```

### Problem: Permission denied on storage
**Solution:**
```bash
# Linux/Mac
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Windows (Run as Administrator)
icacls storage /grant Users:F /t
```

---

## 📊 **Optional Features**

### 1. **Queue Workers** (For Notifications)
```bash
# Start queue worker
php artisan queue:work

# Or use Supervisor (production)
```

### 2. **Task Scheduler** (For SLA Processing)
```bash
# Add to crontab (Linux/Mac)
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### 3. **Redis Cache** (Performance)
```env
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
```

---

## 🌐 **API Documentation**

### Open Data Endpoint
```
GET /api/open-data

Query Parameters:
- category (string): infrastructure, sanitation, safety, etc.
- status (string): pending, in_progress, resolved, rejected
- from_date (date): YYYY-MM-DD
- page (int): pagination

Example:
http://localhost:8000/api/open-data?category=infrastructure&status=resolved&from_date=2025-10-01
```

**Response:**
```json
{
  "data": [
    {
      "id": 123,
      "title": "Pothole on Main Street",
      "category": "infrastructure",
      "status": "resolved",
      "location": "Dhaka, Ward 12",
      "latitude": 23.8103,
      "longitude": 90.4125,
      "created_at": "2025-10-20T10:30:00Z",
      "status_updated_at": "2025-10-22T15:45:00Z"
    }
  ],
  "meta": {
    "total": 1523,
    "per_page": 100,
    "current_page": 1,
    "last_page": 16
  }
}
```

---

## 📞 **Support**

For issues or questions:
- GitHub Issues: (Your repository URL)
- Email: support@chokhe-dekha.gov.bd
- Documentation: See `UPGRADE_SUMMARY.md`

---

## 🎯 **Next Steps**

1. ✅ Set up Google Maps API
2. ✅ Create admin account
3. ✅ Test report creation
4. ✅ View command center
5. ✅ Check transparency board
6. Configure email (optional)
7. Set up queue workers (optional)
8. Deploy to staging/production

---

**Built for Bangladesh 🇧🇩 | জনগণের চোখ, সরকারের কান**

