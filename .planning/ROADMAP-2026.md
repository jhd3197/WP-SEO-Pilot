# WP SEO Pilot - 2026 Development Roadmap

## Vision Statement

WP SEO Pilot is a comprehensive WordPress SEO plugin that prioritizes **practical SEO tools** (redirects, 404 monitoring, sitemaps, breadcrumbs) over AI features. AI functionality is delegated to WP AI Pilot for users who want it, keeping WP SEO Pilot focused on core SEO optimization.

---

## Current State (v0.2.x)

### Existing Features
- SEO title/description management with template variables
- Meta tags & Open Graph output
- JSON-LD schema (Article, Organization, WebSite, LocalBusiness)
- XML Sitemaps with custom configuration
- 404 Error Logging & Monitoring
- Redirect Manager (301, 302, 307, 410)
- SEO Audit with 14-factor scoring
- Internal Linking Rules
- Social Card Generator
- LLM.txt Generator
- Bulk Editor
- Content Gap Analysis
- WP-CLI Support
- Admin Bar SEO Indicator

### What We're Missing (vs Yoast, Rank Math, AIOSEO)
- Breadcrumbs (navigation + schema)
- robots.txt Editor UI
- Multiple Focus Keywords
- IndexNow Integration
- Google Search Console Integration
- Video SEO / Video Schema
- FAQ & HowTo Block Support
- Canonical URL Management UI
- WooCommerce SEO
- Rank/Keyword Tracking
- Image SEO (bulk alt text)
- Multiple Location Local SEO UI

---

## Phase 1: Redirects & 404 Enhancement (Priority: HIGH)

**Goal:** Make WP SEO Pilot the go-to solution for redirect and 404 management.

### 1.1 Redirect Manager V2 ✅ COMPLETED
- [x] Import/export redirects (CSV, JSON)
- [x] Bulk redirect creation from CSV
- [x] Redirect chains detection and warning
- [x] Redirect loops prevention
- [x] Regex redirect support (with backreferences $1, $2, etc.)
- [x] Auto-redirect on slug change (already existed)
- [x] Redirect groups/categories
- [x] Redirect analytics (hit counts, last accessed) (already existed)
- [x] Timed redirects (start/end dates for campaigns)
- [x] Edit existing redirects
- [x] Search/filter redirects
- [x] Bulk delete
- [x] Pagination
- [x] Notes field for admin documentation

### 1.2 404 Monitor Enhancement ✅ COMPLETED
- [x] Real-time 404 dashboard widget
- [x] One-click "Create Redirect" from 404 log
- [x] Automatic redirect suggestions (fuzzy URL matching)
- [x] 404 notifications (email/admin notice thresholds)
- [x] Ignore list for known false positives
- [x] Bot vs human traffic filtering
- [x] Export 404 logs
- [x] Scheduled cleanup of old entries

### 1.3 Link Health Checker ✅ COMPLETED
- [x] Broken internal link scanner
- [x] Broken external link detection
- [x] Orphan page detection (pages with no internal links)
- [x] Link health report dashboard
- [x] Scheduled link scans

---

## Phase 2: Breadcrumbs & Navigation

**Goal:** Provide flexible breadcrumb navigation with full schema support.

### 2.1 Breadcrumbs Core ✅ COMPLETED
- [x] Breadcrumb schema (BreadcrumbList JSON-LD)
- [x] Shortcode `[wpseopilot_breadcrumbs]`
- [x] PHP function `wpseopilot_breadcrumbs()`
- [x] Gutenberg block for breadcrumbs
- [x] Customizable separators (>, /, |, arrow, chevron, custom)
- [x] Show/hide home link option
- [x] Truncate long titles option
- [x] Style presets (default, minimal, rounded, pills, none)
- [x] Settings UI in admin panel

### 2.2 Breadcrumb Customization
- [x] Custom labels per post type
- [x] Custom labels per taxonomy
- [x] Override breadcrumb on individual posts (meta field support)
- [x] Archive breadcrumb customization
- [x] Author archive breadcrumbs
- [x] Date archive breadcrumbs

### 2.2 Breadcrumb Customization ✅ COMPLETED

### 2.3 Theme Integration ✅ COMPLETED
- [x] CSS customization options
- [x] Pre-built style presets (5 presets)
- [x] Accessibility (ARIA) compliance
- [ ] Auto-insertion via theme hooks (future enhancement)

---

## Phase 3: Advanced Schema & Structured Data

**Goal:** Expand schema support to match Rank Math's 20+ types.

### 3.1 Additional Schema Types
- [ ] Video schema (VideoObject)
- [ ] Course schema
- [ ] Software/App schema
- [ ] Book schema
- [ ] Music schema (Album, Playlist)
- [ ] Movie schema
- [ ] Restaurant schema
- [ ] Service schema
- [ ] Job Posting schema
- [ ] Medical schema types

### 3.2 Schema Builder Enhancements
- [ ] Schema templates (reusable presets)
- [ ] Import schema from URL
- [ ] Schema validation tool
- [ ] Multiple schemas per page
- [ ] Conditional schema (show only if conditions met)
- [ ] Custom schema code editor

### 3.3 FAQ & HowTo Integration
- [ ] FAQ block with automatic schema
- [ ] HowTo block with step schema
- [ ] Import existing FAQ content
- [ ] Accordion style options

---

## Phase 4: Search Console & Indexing

**Goal:** Connect to Google and enable instant indexing.

### 4.1 IndexNow Integration ✅ COMPLETED
- [x] Automatic ping on publish/update
- [x] Bulk URL submission
- [x] IndexNow API key management (auto-generated UUID)
- [x] Key file verification endpoint
- [x] Bing, Yandex, Seznam, Naver support (via IndexNow protocol)
- [x] Submission log and status tracking
- [x] Settings UI in admin panel
- [x] REST API for manual submission

### 4.2 Google Search Console Connection
- [ ] OAuth2 authentication
- [ ] Performance data display (clicks, impressions, CTR)
- [ ] Top queries report
- [ ] Index coverage status
- [ ] Submit URLs to Google
- [ ] Crawl stats overview

### 4.3 Instant Indexing
- [ ] Manual "Request Indexing" button per post
- [ ] Bulk indexing requests
- [ ] Indexing queue management
- [ ] Status tracking (indexed, pending, error)

---

## Phase 5: Content Optimization Tools

**Goal:** Provide tools to optimize existing content at scale.

### 5.1 Multi-Keyword Support
- [ ] Support 3-5 focus keywords per post
- [ ] Keyword density for each keyword
- [ ] Keyword placement analysis for each
- [ ] Keyword cannibalization detection

### 5.2 Image SEO
- [ ] Bulk image alt text editor
- [ ] Auto-generate alt text from filename
- [ ] Missing alt text report
- [ ] Image title optimization
- [ ] WebP/image format recommendations

### 5.3 Content Templates
- [ ] Reusable SEO templates
- [ ] Apply template to multiple posts
- [ ] Template variables support
- [ ] Default templates per post type

### 5.4 robots.txt & htaccess Editor
- [ ] Visual robots.txt editor
- [ ] Common rules presets
- [ ] Syntax validation
- [ ] Backup before changes
- [ ] .htaccess editor (with caution warnings)

### 5.5 Canonical URL Manager
- [ ] Canonical URL field in editor
- [ ] Bulk canonical editor
- [ ] Duplicate content detection
- [ ] Cross-domain canonical support

---

## Phase 6: Local & WooCommerce SEO ✅ COMPLETED

**Goal:** Specialized SEO for local businesses and online stores.

### 6.1 Local SEO Enhancement ✅ COMPLETED
- [x] Multiple locations manager UI
- [x] Location-specific schema
- [ ] Individual location pages (future)
- [ ] Store locator integration (future)
- [ ] Local KML sitemap (future)
- [ ] Google Business Profile suggestions (future)

### 6.2 WooCommerce Integration ✅ COMPLETED
- [x] Product schema (full)
- [x] Review/rating schema
- [x] Price/availability schema
- [x] AggregateRating schema
- [ ] Product category SEO (future)
- [ ] Brand schema (future)
- [ ] Merchant listing eligibility (future)

### 6.3 News & Video Sitemaps ✅ COMPLETED
- [x] Google News sitemap
- [x] Video sitemap generation (`/sitemap-video.xml`)
- [x] NewsArticle schema handler
- [x] Video schema (YouTube/Vimeo detection)
- [ ] Publication name settings (future)

---

## Phase 7: Polish, Performance & DX

**Goal:** Optimize performance, improve developer experience, finalize branding.

### 7.1 Branding Consistency
- [ ] "WP SEO Pilot" everywhere
- [ ] Updated plugin header
- [ ] Consistent admin menu labels
- [ ] Updated setup wizard branding
- [ ] Documentation site/pages

### 7.2 Performance Optimization
- [ ] Lazy load admin components
- [ ] Database query optimization
- [ ] Caching layer for schema/sitemaps
- [ ] Reduce frontend footprint
- [ ] Code splitting for admin JS

### 7.3 Developer Experience
- [ ] REST API documentation
- [ ] Filter/action hooks documentation
- [ ] Schema API for custom types
- [ ] Example snippets library
- [ ] PHPDoc improvements

### 7.4 Import/Export & Migration
- [ ] Import from Yoast SEO
- [ ] Import from Rank Math
- [ ] Import from AIOSEO
- [ ] Import from SEOPress
- [ ] Export all settings
- [ ] Site migration tool

### 7.5 Accessibility & i18n
- [ ] WCAG 2.1 AA compliance
- [ ] RTL support improvements
- [ ] Translation-ready strings
- [ ] Language file updates

---

## Priority Order

| Priority | Phase | Reason |
|----------|-------|--------|
| 1 | Phase 1 | Redirects & 404 are core tools to promote |
| 2 | Phase 2 | Breadcrumbs are highly requested and SEO-beneficial |
| 3 | Phase 3 | Schema markup directly impacts rich results |
| 4 | Phase 4 | Search Console integration provides real value |
| 5 | Phase 5 | Content optimization tools are competitive features |
| 6 | Phase 6 | Local/WooCommerce are niche but high-value |
| 7 | Phase 7 | Polish ensures long-term maintainability |

---

## Non-Goals (Delegated to WP AI Pilot)

These features are intentionally NOT part of WP SEO Pilot:
- AI content generation (use WP AI Pilot)
- AI writing assistant (use WP AI Pilot)
- AI-powered suggestions (use WP AI Pilot)
- Chat-based SEO advice (use WP AI Pilot)

WP SEO Pilot will integrate WITH WP AI Pilot for AI features, but will not duplicate them.

---

## Success Metrics

- 404 to redirect conversion rate
- Redirect manager usage (% of users with redirects)
- Breadcrumb adoption rate
- Schema types in use
- Search Console connected accounts
- Average SEO score improvement over time

---

## Competitor Feature Comparison

| Feature | WP SEO Pilot | Yoast | Rank Math | AIOSEO |
|---------|--------------|-------|-----------|--------|
| SEO Titles/Desc | Yes | Yes | Yes | Yes |
| Redirects | Yes | Premium | Free | Pro |
| 404 Monitor | Yes | No | Free | Pro |
| Breadcrumbs | Planned | Yes | Yes | Yes |
| IndexNow | Planned | No | Pro | Pro |
| Search Console | Planned | No | Free | Pro |
| Multi Keywords | Planned | Premium | Free | Pro |
| Local SEO | Basic | Addon | Pro | Pro |
| WooCommerce | Planned | Addon | Pro | Pro |
| Schema Types | 12+ | 5+ | 20+ | 15+ |
| AI Integration | Via WP AI Pilot | Built-in | Built-in | Built-in |

---

*Last Updated: January 2026*
*Version: 2.0*
