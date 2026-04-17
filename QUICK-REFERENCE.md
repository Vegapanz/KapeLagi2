# 🚀 QUICK REFERENCE CARD

## Project Files at a Glance

```
✅ index.php              Main entry point (24 lines)
✅ components/navbar.php  Navigation bar (25 lines)
✅ components/hero.php    Hero section (25 lines)
✅ assets/css/styles.css  All styling (400+ lines)
✅ assets/js/script.js    Interactions (150+ lines)
✅ README.md              Full documentation
✅ SETUP-GUIDE.md         Setup instructions
```

---

## 🏃 Start Server (Choose One)

**PHP Built-in Server:**
```bash
php -S localhost:8000
```

**XAMPP:**
```
htdocs/kapelagi/ → http://localhost/kapelagi/
```

**Any Server:**
```
Upload to host → access via domain
```

---

## 📊 Design Specs

| Element | Value | Font |
|---------|-------|------|
| Navbar BG | #E8E0D0 | Smooch Sans |
| Hero BG | #1A0F0A | - |
| Headline | 7rem | Anton |
| Text | #F5F1E8 | Smooch Sans |
| Subtext | #D6D0C4 | Smooch Sans |

---

## 🎨 Edit Colors (One Place)

**File:** `assets/css/styles.css` (Line 7-12)

```css
:root {
    --color-dark-bg: #1A0F0A;      /* Hero background */
    --color-navbar-bg: #E8E0D0;    /* Navbar background */
    --color-text-primary: #F5F1E8; /* Main text */
    --color-text-secondary: #D6D0C4; /* Secondary text */
    --color-accent: #A17C5C;       /* Hover effects */
}
```

---

## 📝 Edit Text Content

**Navbar Text:**
- File: `components/navbar.php`
- Change menu links in lines 12-21

**Hero Text:**
- File: `components/hero.php`
- Change headline in lines 8-10
- Change paragraph in lines 12-14

---

## 🖼️ Add Images

**Required Files:**
```
assets/images/coffee-cup-1.png
assets/images/coffee-cup-2.png
```

**Specs:**
- Format: PNG (transparent background)
- Size: 400-500px wide, 500-600px tall
- Style: Iced coffee cups

---

## 📱 Responsive Breakpoints

| Device | Width | Headline |
|--------|-------|----------|
| Desktop | 992px+ | 7rem |
| Tablet | 577-991px | 3.5rem |
| Mobile | ≤576px | 2.5rem |

---

## ✨ Interactive Features

| Feature | File | Line |
|---------|------|------|
| Scroll shadow | `script.js` | 5-15 |
| Smooth scroll | `script.js` | 17-35 |
| Hover effects | `script.js` | 37-55 |
| Active links | `script.js` | 57-75 |

---

## 🔗 CDN Links (Auto-Loaded)

```html
Bootstrap 5:
https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css

Google Fonts:
https://fonts.googleapis.com/css2?family=Anton&family=Smooch+Sans:wght@300;400;500&display=swap
```

---

## 🐛 Check Server is Running

```bash
# Test if PHP server is working
curl http://localhost:8000
# Should return HTML content

# Or just open URL
http://localhost:8000
```

---

## 📦 Dependencies

✅ PHP 7.4+
✅ Bootstrap 5.3.0 (CDN)
✅ Google Fonts (CDN)
✅ Vanilla JavaScript (no libraries)

---

## 🎯 Most Common Changes

1. **Change headline text** → `components/hero.php` line 9
2. **Change navbar background** → `styles.css` line 9
3. **Change text color** → `styles.css` line 11
4. **Add new navigation link** → `components/navbar.php` line 16
5. **Adjust font size** → `styles.css` search `.hero-headline`

---

## 🔄 Full Site Structure Pattern

```php
<!-- index.php -->
<?php include 'components/navbar.php'; ?>
<?php include 'components/hero.php'; ?>
<?php include 'components/menu.php'; ?>     <!-- Can add more -->
<?php include 'components/about.php'; ?>    <!-- Can add more -->
<?php include 'components/footer.php'; ?>   <!-- Can add more -->
```

---

## 💾 Save & Deploy

1. Edit files locally
2. Test in browser
3. Upload to web host
4. Visit your domain ✅

---

## ⏱️ Typical Load Time: <2 seconds

---

**All questions answered in README.md or SETUP-GUIDE.md** ☕
