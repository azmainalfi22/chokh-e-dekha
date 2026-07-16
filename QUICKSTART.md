# ⚡ Quick Start Guide - Chokh-e-Dekha

## 🚀 **5-Minute Setup**

### **1. Install Dependencies** (2 min)
```bash
composer install
npm install
```

### **2. Configure Environment** (1 min)
```bash
# Copy environment file
cp .env.example .env  # Linux/Mac
copy .env.example .env  # Windows

# Generate application key
php artisan key:generate
```

### **3. Set Google Maps API Key** (Optional but recommended)
Edit `.env`:
```env
GOOGLE_MAPS_API_KEY=your_api_key_here
```

Get API key: https://console.cloud.google.com/ (Enable "Maps JavaScript API")

### **4. Database Setup** (1 min)
```bash
# Run migrations
php artisan migrate --seed
```

### **5. Build & Launch** (1 min)
```bash
# Build frontend
npm run build

# Start server
php artisan serve
```

---

## 🎯 **Access Your Portal**

### **Public Pages**
- 🏠 Homepage: http://localhost:8000/welcome
- 📊 Transparency: http://localhost:8000/transparency
- 🗺️ All Reports: http://localhost:8000/reports

### **Login & Test**
**Admin:**
- Email: `admin@example.com`
- Password: `password`
- Dashboard: http://localhost:8000/admin/command-center ⭐

**Citizen:**
- Email: `user@example.com`
- Password: `password`
- Dashboard: http://localhost:8000/dashboard

---

## 🗺️ **Google Maps Command Center**

1. Get API Key from Google Cloud Console
2. Add to `.env`: `GOOGLE_MAPS_API_KEY=YOUR_KEY`
3. Run: `php artisan config:clear`
4. Visit: http://localhost:8000/admin/command-center
5. See live map with color-coded report markers!

**Colors:**
- 🔴 Red = SLA Overdue
- 🟡 Yellow = Pending
- 🔵 Blue = In Progress
- 🟢 Green = Resolved

---

## 🐛 **Quick Troubleshooting**

### CSS not loading?
```bash
npm run build
php artisan optimize:clear
```

### Map not showing?
1. Check API key in `.env`
2. Enable "Maps JavaScript API" in Google Cloud
3. Run: `php artisan config:clear`

### Permission errors?
```bash
# Linux/Mac
chmod -R 775 storage bootstrap/cache

# Windows (Run as Admin)
icacls storage /grant Users:F /t
```

---

## 📚 **Full Documentation**

- **Setup**: See `SETUP_INSTRUCTIONS.md`
- **Features**: See `UPGRADE_SUMMARY.md`
- **Report**: See `FINAL_IMPLEMENTATION_REPORT.md`

---

## 🎉 **You're Ready!**

Your national civic portal is now running. Start by:
1. ✅ Logging in as admin
2. ✅ Viewing the command center
3. ✅ Creating a test report
4. ✅ Checking the transparency board

**Built for Bangladesh 🇧🇩 | জনগণের চোখ, সরকারের কান**

