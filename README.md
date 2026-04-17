# Kapelagi Coffee Shop Landing Page

A pixel-perfect, responsive landing page for Kapelagi coffee shop built with PHP, Bootstrap 5, and custom CSS.

---

## 📁 Project Structure

```
kapelagi/
│── index.php                          # Main entry point
│── components/
│   ├── navbar.php                    # Navigation bar component
│   └── hero.php                      # Hero section component
│── assets/
│   ├── css/
│   │   └── styles.css               # Custom styling
│   ├── js/
│   │   └── script.js                # JavaScript interactions
│   └── images/
│       ├── coffee-cup-1.png         # First coffee cup image
│       └── coffee-cup-2.png         # Second coffee cup image
└── README.md                          # This file
```

---

## 🚀 Quick Start

### Prerequisites
- PHP 7.4+ (for local development)
- A web browser
- (Optional) A local server like XAMPP, WAMP, or LAMP

### Installation Steps

1. **Download/Clone the project** to your web root directory (e.g., `htdocs/` for XAMPP)

2. **Add coffee cup images** to `assets/images/`:
   - Place your coffee cup images as:
     - `assets/images/coffee-cup-1.png`
     - `assets/images/coffee-cup-2.png`
   - See Image Specifications below

3. **Start a local server**:
   - **XAMPP**: Place in `htdocs/kapelagi/` and visit `http://localhost/kapelagi/`
   - **PHP Built-in Server**: 
     ```bash
     php -S localhost:8000
     ```
   - Then visit `http://localhost:8000`

4. **Open in browser** and you should see the landing page!

---

## 🖼️ Image Specifications

### Coffee Cup Images

**File Names & Sizes:**
- `coffee-cup-1.png` - Iced coffee cup with coffee logo
- `coffee-cup-2.png` - Iced coffee cup with "KAPELAGI SINCE 2024" label

**Image Requirements:**
- **Format**: PNG with transparency
- **Dimensions**: 400-500px width × 500-600px height (minimum)
- **Style**: 
  - Transparent plastic cups with visible iced coffee + milk swirl
  - Clear visibility of coffee branding
  - Professional product photography style
- **Background**: Transparent (PNG with alpha channel)
- **Quality**: High resolution (300+ DPI recommended)

**Positioning Info:**
- Cup 1: Will be rotated -15deg and positioned left
- Cup 2: Will be rotated +8deg and positioned right
- Both have drop shadows applied via CSS
- They overlap slightly for visual interest

**Don't Have Images?**
Create placeholder PNG files temporarily:
1. Create 500×600px transparent PNG files
2. Add a simple coffee cup silhouette or stock image
3. Replace with professional images later

---

## 🎨 Design Colors

| Element | Color Code | Usage |
|---------|-----------|-------|
| Dark Background | `#1A0F0A` | Hero section background |
| Navbar Background | `#E8E0D0` | Light beige navbar |
| Primary Text | `#F5F1E8` | Headings, main text |
| Secondary Text | `#D6D0C4` | Paragraph text |
| Accent | `#A17C5C` | Hover effects, highlights |

---

## 🔤 Typography

### Fonts (Google Fonts)
- **Headings**: `Anton` (font-weight: 900)
  - Main headline: 7rem (responsive)
  
- **Body**: `Smooch Sans` (font-weight: 300, 400, 500)
  - Navigation: 1rem weight-400
  - Paragraph: 1.1rem weight-300
  - Logo: 1.5rem weight-500

*Fonts are automatically loaded from Google CDN in `index.php`*

---

## 📱 Responsive Breakpoints

| Device | Resolution | Changes |
|--------|-----------|---------|
| Desktop | 992px+ | Two-column layout, 7rem headline |
| Tablet | 577-991px | Two-column → stacked, 3.5rem headline |
| Mobile | ≤576px | Full-width stack, 2.5rem headline |
| Extra Small | ≤360px | 2rem headline, optimized spacing |

---

## ⚡ Key Features

### Navbar
- ✅ Sticky positioning
- ✅ Responsive mobile toggle
- ✅ Smooth hover effects with underline animation
- ✅ Pill-shaped "Sign in" button with border
- ✅ Shadow effect on scroll

### Hero Section
- ✅ Full-height viewport on desktop
- ✅ Animated headline with fade-in effect
- ✅ Responsive coffee cup images
- ✅ Hover animations on cups
- ✅ Drop shadows and overlapping layout
- ✅ Dark gradient background

### JavaScript Enhancements
- ✅ Navbar shadow on scroll
- ✅ Smooth scroll navigation
- ✅ Coffee cup hover interactions
- ✅ Active link tracking
- ✅ Mobile navbar auto-close on link click
- ✅ Accessibility improvements

### CSS Features
- ✅ CSS variables for easy theming
- ✅ Smooth transitions and animations
- ✅ Modern gradient backgrounds
- ✅ Flexbox layout system
- ✅ Mobile-first responsive design

---

## 🔧 Customization

### Change Colors
Edit CSS variables in `assets/css/styles.css`:
```css
:root {
    --color-dark-bg: #1A0F0A;      /* Background color */
    --color-navbar-bg: #E8E0D0;    /* Navbar color */
    --color-text-primary: #F5F1E8; /* Main text */
    --color-text-secondary: #D6D0C4; /* Secondary text */
    --color-accent: #A17C5C;       /* Hover/accent color */
}
```

### Modify Text
- **Navbar**: Edit `components/navbar.php`
- **Hero**: Edit `components/hero.php`
- **Styles**: Edit `assets/css/styles.css`

### Add New Sections
1. Create a new PHP component (e.g., `components/menu.php`)
2. Include it in `index.php`:
   ```php
   <?php include 'components/menu.php'; ?>
   ```

---

## 📦 Dependencies

### External Libraries
- **Bootstrap 5.3.0** - CSS Framework (CDN)
- **Google Fonts** - Anton & Smooch Sans (CDN)

### Internal Files
- **PHP 7.4+** - Server-side processing
- **Vanilla JavaScript** - No jQuery or other frameworks

---

## ✅ Browser Support

- ✅ Chrome (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Edge (latest)
- ✅ Mobile browsers (iOS Safari, Chrome mobile)

---

## 🚨 Troubleshooting

### Images Not Showing
1. Check that image files exist in `assets/images/`
2. Verify file names match exactly:
   - `coffee-cup-1.png`
   - `coffee-cup-2.png`
3. Check browser console for 404 errors
4. Ensure images have transparent backgrounds (PNG)

### Fonts Not Loading
1. Check internet connection (fonts load from CDN)
2. Verify Google Fonts link in `index.php`:
   ```html
   <link href="https://fonts.googleapis.com/css2?family=Anton&family=Smooch+Sans:wght@300;400;500&display=swap" rel="stylesheet">
   ```
3. Clear browser cache and reload

### Layout Issues
1. Clear browser cache (Ctrl+Shift+Delete)
2. Hard refresh (Ctrl+F5)
3. Check Bootstrap CDN link is loading properly
4. Verify CSS file is linked correctly in `index.php`

### Mobile Not Responsive
1. Verify `<meta name="viewport">` tag exists in `index.php`
2. Check viewport meta tag: 
   ```html
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   ```
3. Clear browser cache and test in private/incognito mode

---

## 📄 File Descriptions

### index.php
The main entry point that includes all components and loads Bootstrap, Google Fonts, CSS, and JavaScript.

### components/navbar.php
Navigation bar with logo, menu links, and sign-in button.
- Sticky positioning
- Mobile responsive toggle
- Smooth hover animations

### components/hero.php
Hero section with headline, paragraph, and coffee cup images.
- Responsive two-column layout
- Animation on page load
- Interactive coffee cup hover effects

### assets/css/styles.css
Complete custom styling including:
- Global variables and reset
- Navbar styles with animations
- Hero section responsive design
- Mobile-first breakpoints
- Accessibility features

### assets/js/script.js
JavaScript enhancements:
- Navbar shadow on scroll
- Smooth scroll navigation
- Coffee cup hover interactions
- Active link tracking
- Mobile navbar management
- Accessibility improvements

---

## 🎯 Next Steps

1. **Add Images**: Replace placeholder images with professional coffee cup photos
2. **Update Content**: Customize text, email, phone numbers as needed
3. **Add More Sections**: Create additional pages (Menu, About, Contact)
4. **Add Links**: Update navbar links to point to actual pages
5. **Deploy**: Upload to a web server

---

## 📞 Support

For customization help or modifications, refer to:
- CSS Variables in `styles.css` for styling changes
- PHP components for content changes
- `script.js` for interactive feature adjustments

---

## 📝 License

This template is provided as-is for use with Kapelagi Coffee Shop.

---

**Last Updated**: April 2026
**Version**: 1.0.0
