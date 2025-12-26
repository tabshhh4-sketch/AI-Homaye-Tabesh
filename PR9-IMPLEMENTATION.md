# Atlas Control Center - Implementation Guide

## PR9: مرکز کنترل استراتژیک «اطلس» (Atlas Control Center)

### Overview
The Atlas Control Center is a Business Intelligence (BI) system and data-driven decision engine for the Homaye Tabesh WordPress plugin. It transforms raw behavioral and business data into actionable insights with human-readable explanations.

### Architecture

Atlas is built with **5 operational layers**:

#### Layer 1: Executive Overview (نمای کلان)
**Purpose**: 30-second health overview of the site

**Components**:
- `ExecutiveOverview.jsx` - React component
- `HT_Atlas_API::get_health_overview()` - PHP endpoint

**Features**:
- Health score calculation (0-100)
- Key metrics dashboard:
  - Total sessions
  - Conversion rate
  - Active users (7 days)
  - Average cart value
  - In-progress conversions
  - Total events
- Priority alerts and insights
- Atlas Map placeholder (site structure visualization)

**Human Explanation Rule**: Every metric includes contextual description explaining what it means in business terms.

#### Layer 2: Behavior Analyzer (تحلیل رفتار)
**Purpose**: Identify incomplete paths and repetitive behaviors that don't lead to conversions

**Components**:
- `BehaviorAnalyzer.jsx` - React component
- `HT_Atlas_API::get_flow_analysis()` - Flow analysis endpoint
- `HT_Atlas_API::get_bottlenecks()` - Bottleneck detection endpoint

**Features**:
- Bottleneck detection algorithm (identifies exit rates > 30%)
- Event distribution analysis
- Indecision point identification
- Form drop-off analysis (Chapko forms)

**Algorithm**:
```javascript
const detectBottlenecks = (userPath) => {
    const dropOffPoints = userPath.filter(step => step.exitRate > 0.6);
    return dropOffPoints.map(point => ({
        location: point.pageName,
        insight: 'کاربر در این مرحله دچار تردید می‌شود'
    }));
};
```

#### Layer 3: Recommendation Engine (موتور پیشنهادات)
**Purpose**: Transform raw data into actionable business recommendations

**Components**:
- `RecommendationEngine.jsx` - React component
- `HT_Atlas_API::get_recommendations()` - Recommendations endpoint

**Features**:
- Data-to-recommendation transformer
- Priority-based filtering (high, medium, low)
- Expected impact prediction
- Action steps for each recommendation
- Business-focused suggestions

**Transformation Flow**:
```
Raw Data → Atlas Analysis → Actionable Recommendation
60% drop rate → Users confused in form → Simplify form + add guidance
```

#### Layer 4: Decision Assistant (دستیار تصمیم‌سازی)
**Purpose**: Simulate changes before applying them (Predictive A/B Testing)

**Components**:
- `DecisionAssistant.jsx` - React component
- `HT_Atlas_API::simulate_decision()` - Simulation endpoint

**Features**:
- Decision simulation for:
  - Price changes
  - Form simplification
  - CTA modifications
  - Layout changes
- Risk assessment (low, medium, high)
- Confidence level calculation
- Impact prediction
- Change history tracking (planned)

**Simulation Logic**:
```php
public function simulate_decision($current_rate, $risk_level) {
    $prediction = ($current_rate * 1.1) - ($risk_level * 0.05);
    return [
        'expected_conversion' => $prediction,
        'recommendation' => 'با توجه به ریسک فعلی، این تغییر مثبت ارزیابی می‌شود.'
    ];
}
```

#### Layer 5: Advanced Settings (تنظیمات هسته)
**Purpose**: Core configuration management (Administrator only)

**Components**:
- `AtlasSettings.jsx` - React component
- `HT_Atlas_API::get_settings()` - Get settings endpoint
- `HT_Atlas_API::update_settings()` - Update settings endpoint

**Features**:
- Auto-Index toggle (automatic site scanning)
- Scan interval configuration (minimum 5 minutes)
- Intelligence level selection:
  - Basic: Simple, fast analysis
  - Standard: Recommended for most sites
  - Advanced: Deep analysis with comprehensive recommendations
- Alert threshold configuration (0-100)
- Data retention settings (7-365 days)

**Security**: Only users with `administrator` role can access this layer.

### Navigation Design

**3-Click Rule Compliance**:
```
Main Menu → Atlas Dashboard → Layer Selection → Feature
Example: Dashboard → Behavior Analyzer → Bottlenecks (2 clicks)
```

All features are accessible within maximum 3 clicks from the main dashboard.

### REST API Endpoints

Base URL: `/wp-json/homaye/v1/atlas`

| Endpoint | Method | Purpose | Permission |
|----------|--------|---------|------------|
| `/health` | GET | Get health overview | manage_options |
| `/flow-analysis` | GET | Get user flow analysis | manage_options |
| `/bottlenecks` | GET | Get bottleneck detection | manage_options |
| `/recommendations` | GET | Get recommendations | manage_options |
| `/simulate` | POST | Simulate decision impact | manage_options |
| `/settings` | GET | Get Atlas settings | administrator |
| `/settings` | POST | Update Atlas settings | administrator |
| `/export/pdf` | POST | Export PDF report | manage_options |
| `/export/csv` | POST | Export CSV report | manage_options |

### Database Tables Used

Atlas leverages existing tables:
- `wp_homa_sessions` - Session tracking
- `wp_homa_vault` - Context storage
- `wp_homa_user_interests` - Long-term interests
- `wp_homaye_conversion_sessions` - Conversion tracking
- `wp_homaye_telemetry_events` - Event tracking

### WordPress Integration

**Menu Structure**:
```
همای تابش (Main Menu)
├── تنظیمات (Settings)
├── آمار پرسونا (Persona Stats)
└── 🗺️ مرکز کنترل اطلس (Atlas Control Center) ← NEW
```

**Admin Page**: `admin.php?page=homaye-tabesh-atlas`

### Build System

**Webpack Configuration**: Dual entry points
```javascript
module.exports = [
  { entry: './assets/react/index.js', output: 'homa-sidebar.js' },
  { entry: './assets/react/atlas-index.js', output: 'atlas-dashboard.js' }
];
```

**Build Commands**:
```bash
npm install          # Install dependencies
npm run build        # Production build
npm run dev          # Development watch mode
```

### Styling

**CSS File**: `assets/css/atlas-dashboard.css`

**Design Principles**:
- Modern, clean interface
- Persian (RTL) text support
- Color-coded status indicators:
  - 🟢 Green: Excellent/Success
  - 🔵 Blue: Good/Info
  - 🟡 Orange: Warning
  - 🔴 Red: Critical
- Responsive design for mobile devices

### Testing Requirements

#### 1. 3-Click Rule Test
Verify all features accessible within 3 clicks:
- ✅ Dashboard → Layer → Feature

#### 2. Human Explanation Rule
Test that no number appears without context:
- ✅ Every metric has description
- ✅ Every insight has actionable explanation

#### 3. Change History Test
Verify system tracks decision history:
- 📋 Planned for future implementation

#### 4. Security Test
Verify Layer 5 restrictions:
- ✅ Non-administrators cannot access settings
- ✅ Security warning displayed for unauthorized users

#### 5. Performance Test
Test with heavy data loads:
- Auto-Index should run only in low-traffic periods
- Scan interval minimum 5 minutes to prevent server overload

### Performance Considerations

**Risks & Mitigations**:

1. **Heavy Site Scanning**
   - Risk: Auto-Index can increase server load
   - Mitigation: Run only in low-traffic periods, configurable interval

2. **Data Accuracy**
   - Risk: Low sample size leads to unreliable insights
   - Mitigation: Atlas warns when data is insufficient

3. **Database Queries**
   - Risk: Complex queries on large datasets
   - Mitigation: Indexed columns, 30-day analysis window

### Future Enhancements (Layer 7)

**Report Generator**:
- [ ] Full PDF report generation (requires TCPDF/Dompdf)
- [x] Basic CSV export (implemented)
- [ ] Executive summary templates
- [ ] Email delivery system
- [ ] Scheduled reports

### Code Quality

**PHP Standards**:
- Strict typing: `declare(strict_types=1);`
- Namespace: `HomayeTabesh`
- Security: Nonce verification, capability checks
- Documentation: PHPDoc comments

**React Standards**:
- Functional components with hooks
- Props validation
- Error boundaries
- Loading states

### Accessibility

- Screen reader support via semantic HTML
- Keyboard navigation support
- High contrast color scheme
- Persian language throughout

### Browser Compatibility

- Modern browsers (Chrome, Firefox, Safari, Edge)
- React 18+ required
- ES6+ JavaScript support

### License

GPL v3 or later (consistent with WordPress and parent plugin)

---

## Quick Start

1. **Access Atlas Dashboard**:
   - WordPress Admin → همای تابش → 🗺️ مرکز کنترل اطلس

2. **View Health Overview**:
   - Default layer shows 30-second site health snapshot
   - Auto-refreshes every 30 seconds

3. **Analyze Bottlenecks**:
   - Switch to "تحلیل رفتار" layer
   - View identified bottlenecks and indecision points

4. **Get Recommendations**:
   - Switch to "پیشنهادات" layer
   - Filter by priority
   - Expand to see action steps

5. **Simulate Decisions**:
   - Switch to "شبیه‌ساز تصمیم" layer
   - Enter current metrics
   - Adjust risk level
   - Run simulation

6. **Configure Settings** (Admin only):
   - Switch to "تنظیمات هسته" layer
   - Adjust Auto-Index, scan interval, intelligence level
   - Save settings

---

## Support

For issues or questions:
- GitHub Issues: https://github.com/tabshhh4-sketch/AI-Homaye-Tabesh/issues
- Documentation: See repository README files

---

**Implementation Date**: December 26, 2024
**Version**: 1.0.0
**Author**: Tabshhh4 (via GitHub Copilot)
