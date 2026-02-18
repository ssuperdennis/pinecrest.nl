# PineCrest Website - Quick Deployment Guide

## What's Included

✅ Professional consultancy website for PineCrest
✅ Custom SVG logo (pine tree design with gradient)
✅ Fully responsive design (mobile, tablet, desktop)
✅ Contact form with email functionality
✅ Privacy policy page
✅ PHP 8.4+ compatible
✅ Ready to upload to any web host

## Upload Instructions

### Step 1: Prepare Files
Download/upload the following files to your web host:

```
public_html/
├── .htaccess
├── robots.txt
├── index.php
├── privacy.php
├── send-mail.php
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── main.js
└── README.md
```

### Step 2: Update Email Address

Edit `send-mail.php` line 9:
```php
$recipientEmail = 'info@pinecrest.nl'; // Replace with your email
```

### Step 3: Upload to Host

**Using FTP/SFTP:**
- Connect to your hosting account
- Navigate to `public_html` or `www`
- Upload all files maintaining the folder structure

**Using Hosting Control Panel (cPanel, DirectAdmin, etc.):**
- Open File Manager
- Navigate to public_html
- Upload and extract the zip file

### Step 4: Test

1. Visit your domain: `https://www.pinecrest.nl`
2. Test the contact form
3. Check that emails arrive (check spam folder)

## Host Recommendations

This site works with any PHP 8.4+ hosting including:
- SiteGround
- Bluehost
- Hostinger
- Namecheap
- TransIP (Dutch)
- Your own VPS with Apache/Nginx + PHP

## Support

If you encounter issues:
- Check PHP version is 8.4 or higher
- Verify mail() function is enabled
- Check file permissions (644 for files, 755 for folders)
- Review .htaccess is supported by your host

## Features Summary

| Feature | Description |
|---------|-------------|
| Design | Clean, business-focused with pine tree branding |
| Colors | Professional green palette (#1a472a, #2d5a3d, #3d7a52) |
| Fonts | Inter (Google Fonts) |
| Responsive | Mobile-first design |
| Contact Form | With validation and auto-reply |
| SEO | Semantic HTML, meta tags included |
| Performance | Minimal dependencies, fast loading |

---
Created for PineCrest Project Management Consultancy
Wenum Wiesel, The Netherlands
www.pinecrest.nl
