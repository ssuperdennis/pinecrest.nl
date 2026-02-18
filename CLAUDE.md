# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

PineCrest is a Critical Chain Project Management consultancy website with two main components:
1. **Consultancy services** - Professional PM services at €165/hour
2. **ProjectFlow product** - An upcoming open source PM tool (Django 5 + Python 3.12) launching May 2026

The site is a static PHP website that can be deployed to any shared hosting with PHP 8.4+ support.

## Development Setup

This is a static PHP site with no build process or dependencies:

- **Local development**: Use any PHP development server (e.g., `php -S localhost:8000`)
- **No package managers**: No npm, composer, or other dependency management
- **Deployment**: Upload files directly to web host via FTP/SFTP or control panel

To test the contact forms locally, you'll need PHP's mail() function configured or use a mail testing service like Mailtrap.

## Architecture

### File Structure
```
/
├── index.php           # Main landing page (all sections in one file)
├── privacy.php         # Privacy policy page
├── send-mail.php       # Contact form handler (POST endpoint)
├── beta-signup.php     # Beta signup form handler (POST endpoint)
├── assets/
│   ├── css/
│   │   └── style.css   # All styles (CSS variables for theming)
│   ├── js/
│   │   └── main.js     # All interactive functionality
│   └── images/
│       ├── logo.png
│       └── logo-high-res.png
├── .htaccess           # Apache configuration
└── robots.txt
```

### Key Design Patterns

**Single-Page Navigation**: The main page uses anchor-based navigation (`#services`, `#contact`, etc.) with smooth scrolling. All sections are in `index.php`.

**CSS Architecture**:
- CSS variables in `:root` define the color scheme and design tokens
- Mobile-first responsive design with breakpoints at 968px, 768px, and 480px
- BEM-like naming for components (`.service-card`, `.hero-title`, etc.)

**Form Handling**:
- Both forms (contact and beta signup) use honeypot anti-spam technique (hidden `website` field)
- PHP endpoints return JSON responses
- JavaScript fetch API handles form submission with success/error states
- Auto-reply emails are sent to form submitters

**JavaScript Functionality**:
- Rotating hero slogans (changes every 4 seconds)
- Mobile navigation toggle
- Intersection Observer for scroll animations
- Active nav link highlighting based on scroll position
- Form validation and submission

### Configuration

**Email recipient**: Set in both `send-mail.php:8` and `beta-signup.php:8`:
```php
$recipientEmail = 'info@pinecrest.nl';
```

**Color scheme**: Edit CSS variables in `assets/css/style.css:8-24`:
```css
:root {
    --primary-dark: #0f172a;
    --primary: #1e3a5f;
    --accent: #3b82f6;
    /* ... */
}
```

## Common Tasks

### Updating content
- Edit `index.php` directly for main page content
- Services, use cases, and other sections are clearly commented with HTML section markers

### Adding a new section
1. Add HTML section in `index.php` with appropriate ID
2. Add link to nav menu (line 27-32)
3. Add styles in `assets/css/style.css` following existing patterns
4. If section needs animation, add selector to `animatedElements` in `main.js:100-102`

### Modifying the logo
The logo is a PNG image. Replace:
- `assets/images/logo.png` (used in navbar)
- `assets/images/logo-high-res.png` (used in footer)

### Form changes
- Contact form: `index.php:488-535` and handler in `send-mail.php`
- Beta signup: `index.php:283-336` and handler in `beta-signup.php`

## Deployment Checklist

Before deploying:
1. Update `$recipientEmail` in both PHP form handlers
2. Uncomment HTTPS redirect in `.htaccess:29-34` if SSL is configured
3. Test both forms to ensure mail() function works on hosting
4. Verify all asset paths are correct (relative paths used throughout)
5. Check that .htaccess is supported by your host

## Server Requirements

- PHP 8.4 or higher
- mail() function enabled (or SMTP integration)
- Apache server (for .htaccess support) or configure equivalent on Nginx
- No database required

## Security Notes

- Honeypot fields protect forms from spam (hidden `website` fields)
- All user input is sanitized through `cleanInput()` function
- .htaccess prevents directory browsing and protects sensitive files
- Security headers set via .htaccess (X-Frame-Options, X-XSS-Protection, etc.)
- No sensitive data should be committed (email is configured per deployment)
