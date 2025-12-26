# 🗺️ Atlas Control Center (مرکز کنترل اطلس)

**Business Intelligence & Data-Driven Decision Engine**  
**سیستم هوش تجاری و موتور تصمیم‌گیری داده‌محور**

---

## Overview / معرفی

Atlas Control Center is a comprehensive BI (Business Intelligence) dashboard that transforms raw behavioral data into actionable business insights with human-readable explanations.

مرکز کنترل اطلس یک داشبورد جامع هوش تجاری است که داده‌های رفتاری خام را به بینش‌های تجاری قابل اجرا با توضیحات قابل فهم برای انسان تبدیل می‌کند.

---

## 🎯 Key Features / ویژگی‌های کلیدی

### ✅ Five Operational Layers / پنج لایه عملیاتی

1. **📊 Executive Overview (نمای کلان)**
   - 30-second health dashboard
   - Real-time metrics and alerts
   - Health score: 0-100

2. **🔍 Behavior Analyzer (تحلیل رفتار)**
   - Bottleneck detection
   - User flow intelligence
   - Indecision point identification

3. **💡 Recommendation Engine (موتور پیشنهادات)**
   - Data-to-recommendation transformer
   - Priority-based insights
   - Actionable business suggestions

4. **🎯 Decision Assistant (دستیار تصمیم‌سازی)**
   - Predictive A/B testing
   - Decision simulation
   - Risk assessment

5. **⚙️ Advanced Settings (تنظیمات هسته)**
   - Auto-Index configuration
   - Intelligence level control
   - Administrator-only access

### 🚀 Additional Features

- ✅ **3-Click Rule**: All features within 3 clicks
- ✅ **Human Explanations**: Every metric with context
- ✅ **Security**: Role-based access control
- ✅ **Performance**: Optimized for heavy loads
- ✅ **Real-time**: Auto-refresh capabilities
- ✅ **Persian Support**: Full RTL language support

---

## 📋 Requirements / پیش‌نیازها

### System Requirements
- **WordPress**: 6.0+
- **PHP**: 8.2+
- **React**: 18+
- **Node.js**: For building (development only)

### Plugin Dependencies
- Homaye Tabesh Core
- PR7: Omni-Store Infrastructure
- PR8: Enhanced Tracking

---

## 🚀 Installation / نصب

### 1. Ensure Dependencies
```bash
# Install Node.js dependencies
npm install

# Install Composer dependencies
composer install
```

### 2. Build Assets
```bash
# Production build
npm run build

# Development build with watch
npm run dev
```

### 3. Activate
The Atlas Control Center is automatically available after plugin activation.

---

## 📖 Usage / نحوه استفاده

### Access Atlas / دسترسی به اطلس

**WordPress Admin Path:**
```
WordPress Admin → همای تابش → 🗺️ مرکز کنترل اطلس
```

**Direct URL:**
```
/wp-admin/admin.php?page=homaye-tabesh-atlas
```

### Quick Start Guide

#### Daily Routine (30 seconds)
1. Access Executive Overview
2. Check health score
3. Review priority alerts

#### Weekly Review (15 minutes)
1. Analyze user behavior bottlenecks
2. Review recommendations
3. Implement high-priority suggestions

#### Before Major Decisions
1. Use Decision Assistant
2. Run simulation with risk assessment
3. Review predicted impact
4. Make informed decision

---

## 🏗️ Architecture / معماری

### Backend (PHP)

**Main File:** `includes/HT_Atlas_API.php`

**REST API Endpoints:**
```
/wp-json/homaye/v1/atlas/health              [GET]  - Health overview
/wp-json/homaye/v1/atlas/flow-analysis       [GET]  - Flow analysis
/wp-json/homaye/v1/atlas/bottlenecks         [GET]  - Bottleneck detection
/wp-json/homaye/v1/atlas/recommendations     [GET]  - Recommendations
/wp-json/homaye/v1/atlas/simulate            [POST] - Decision simulation
/wp-json/homaye/v1/atlas/settings            [GET]  - Get settings
/wp-json/homaye/v1/atlas/settings            [POST] - Update settings
/wp-json/homaye/v1/atlas/export/csv          [POST] - Export CSV
```

### Frontend (React)

**Components:**
```
assets/react/atlas-components/
├── AtlasDashboard.jsx          # Main container
├── ExecutiveOverview.jsx       # Layer 1
├── BehaviorAnalyzer.jsx        # Layer 2
├── RecommendationEngine.jsx    # Layer 3
├── DecisionAssistant.jsx       # Layer 4
└── AtlasSettings.jsx           # Layer 5
```

### Database Tables

Uses existing tables:
- `wp_homa_sessions`
- `wp_homa_vault`
- `wp_homaye_conversion_sessions`
- `wp_homaye_telemetry_events`

**No new tables created** - leverages PR7 & PR8 infrastructure.

---

## 🔒 Security / امنیت

### Role-Based Access Control

**All Layers (1-4):**
- Required capability: `manage_options`
- Typically: Administrator, Editor, Author roles

**Layer 5 (Advanced Settings):**
- Required role: `administrator`
- Security check: `wp_get_current_user()->roles`
- Warning displayed for unauthorized users

### API Security
- ✅ Nonce verification on all requests
- ✅ Capability checks on all endpoints
- ✅ Input sanitization
- ✅ Output escaping

---

## ⚡ Performance / کارایی

### Optimizations

**Auto-Index Configuration:**
```php
// Recommended settings
Auto-Index: Enabled
Scan Interval: 3600 seconds (60 minutes)
Low-traffic periods only
```

**Health Overview:**
```javascript
// Auto-refresh every 30 seconds
Auto-refresh: Enabled (Layer 1 only)
Analysis window: Last 30 days
```

### Performance Considerations

- ⚠️ Auto-Index can increase server load
- ✅ Configurable scan intervals (min 5 minutes)
- ✅ Analysis limited to 30-day window
- ✅ Database queries use indexed columns

---

## 📊 Algorithms / الگوریتم‌ها

### 1. Health Score Calculation
```php
health_score = 
    (conversion_rate * 0.4) +    // 40% weight
    (active_users * 0.35) +      // 35% weight
    (total_events * 0.25);       // 25% weight
```

**Score Interpretation:**
- 🟢 80-100: Excellent
- 🔵 60-79: Good
- 🟡 40-59: Warning
- 🔴 0-39: Critical

### 2. Bottleneck Detection
```php
if (exit_rate > 60%) {
    severity = 'high';
} elseif (exit_rate > 40%) {
    severity = 'medium';
} else {
    severity = 'low';
}
```

### 3. Decision Simulation
```php
prediction = (current_value * 1.1) - (risk_level * 0.05);
confidence = 100 - (risk_level * 30);
```

---

## 🧪 Testing / تست

### Unit Tests
```bash
# Run PHP tests (if available)
composer test

# Run JavaScript tests (if available)
npm test
```

### Manual Testing Checklist

- [ ] Access dashboard successfully
- [ ] All 5 layers load without errors
- [ ] Health metrics display correctly
- [ ] Bottlenecks detected accurately
- [ ] Recommendations are actionable
- [ ] Simulation produces results
- [ ] Settings save successfully (admin only)
- [ ] Non-admins cannot access Layer 5
- [ ] API endpoints respond correctly
- [ ] Mobile responsive design works

---

## 🔧 Configuration / پیکربندی

### Default Settings

```php
// Default configuration
$defaults = [
    'auto_index_enabled' => false,
    'scan_interval' => 3600,              // 1 hour
    'intelligence_level' => 'standard',   // basic, standard, advanced
    'alert_threshold' => 40,              // 0-100
    'data_retention_days' => 90,          // 7-365
];
```

### Recommended Settings

**Small Sites (<1000 visitors/month):**
```
Auto-Index: Disabled
Scan Interval: N/A
Intelligence: Basic
Alert Threshold: 30
Data Retention: 30 days
```

**Medium Sites (1K-10K visitors/month):**
```
Auto-Index: Enabled
Scan Interval: 3600 seconds (1 hour)
Intelligence: Standard
Alert Threshold: 40
Data Retention: 90 days
```

**Large Sites (>10K visitors/month):**
```
Auto-Index: Enabled
Scan Interval: 7200 seconds (2 hours)
Intelligence: Advanced
Alert Threshold: 50
Data Retention: 180 days
```

---

## 📚 Documentation / مستندات

### Available Guides

1. **PR9-IMPLEMENTATION.md** - Technical implementation details
2. **PR9-QUICKSTART.md** - User quick start guide (Persian & English)
3. **PR9-SUMMARY.md** - Complete project summary
4. **PR9-README.md** - This file

### API Documentation

See `includes/HT_Atlas_API.php` for inline PHPDoc comments.

---

## 🐛 Troubleshooting / عیب‌یابی

### Common Issues

**Issue: Dashboard not loading**
```bash
# Solution: Rebuild assets
npm run build
```

**Issue: No data displayed**
```
# Solutions:
1. Ensure tracking is enabled in settings
2. Wait 7+ days for data collection
3. Verify site has active traffic
```

**Issue: Cannot access Layer 5**
```
# Solution: Verify user role
Only users with 'administrator' role can access Layer 5
```

**Issue: API returns 403 Forbidden**
```
# Solution: Check permissions
Ensure user has 'manage_options' capability
```

---

## 🚀 Future Enhancements / بهبودهای آینده

### Planned Features

- [ ] Full PDF report generation
- [ ] Interactive Atlas Map visualization
- [ ] Change history tracking
- [ ] Email delivery for reports
- [ ] Scheduled reports
- [ ] Advanced A/B testing
- [ ] Machine learning predictions
- [ ] Multi-language support

---

## 🤝 Contributing / مشارکت

We welcome contributions! Please:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

### Coding Standards

- **PHP**: WordPress Coding Standards
- **JavaScript**: ESLint with React rules
- **CSS**: BEM naming convention
- **Documentation**: Clear comments in code

---

## 📝 License / مجوز

GPL v3 or later

---

## 👥 Credits / اعتبار

**Author:** Tabshhh4  
**Implementation:** GitHub Copilot  
**Date:** December 26, 2024  
**Version:** 1.0.0  

Built upon the Homaye Tabesh plugin infrastructure (PR7 & PR8).

---

## 📞 Support / پشتیبانی

**Issues:** https://github.com/tabshhh4-sketch/AI-Homaye-Tabesh/issues  
**Documentation:** See repository files  
**Community:** Coming soon

---

## 🔗 Related PRs

- **PR7**: Omni-Store Memory Engine
- **PR8**: Enhanced Tracking System
- **PR9**: Atlas Control Center (this PR)

---

**Last Updated:** December 26, 2024  
**Status:** ✅ Production Ready
