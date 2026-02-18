# PineCrest Website

## Installation Instructions

### 1. File Upload

Upload all files to your web host using FTP, SFTP, or your hosting control panel's file manager. The files should be uploaded to the public web directory (usually `public_html`, `www`, or `htdocs`).

Required file structure:
```
/
├── index.php
├── privacy.php
├── send-mail.php
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── main.js
├── README.md (this file - optional)
```

### 2. Email Configuration

Open `send-mail.php` and update the following line with your actual email address:

```php
$recipientEmail = 'info@pinecrest.nl'; // Change this to your email
```

### 3. PHP Requirements

Your hosting must support PHP 8.4 or higher with the `mail()` function enabled. Most web hosts have this enabled by default.

### 4. Permissions

No special file permissions are required. The website should work with default permissions.

### 5. Testing

After uploading, test your website by:
1. Visiting your domain (e.g., www.pinecrest.nl)
2. Testing the contact form to ensure emails are sent
3. Checking all navigation links

## Troubleshooting

### Contact form not sending emails:

1. **Check PHP mail function**: Contact your hosting provider to ensure the `mail()` function is enabled
2. **Spam folder**: Check your spam/junk folder
3. **Email delivery**: Some hosts require specific configurations. If emails aren't being delivered, you may need to use SMTP instead.

### To use SMTP instead of the default mail() function:

Replace the mail() calls in `send-mail.php` with a library like PHPMailer. Most hosting providers support SMTP.

### Styling not loading:

1. Ensure the `assets/` folder and its contents were uploaded correctly
2. Check that the file permissions allow reading (usually 644 for files, 755 for directories)

## Customization

### Logo
The logo is embedded as an SVG in the HTML. To customize:
1. Open `index.php` or `privacy.php`
2. Find the `<svg>` element within the `.logo` class
3. Replace or modify the SVG code

### Colors
Edit `assets/css/style.css` and modify the CSS variables in the `:root` selector:
- `--primary-dark`: Dark green (#1a472a)
- `--primary`: Primary green (#2d5a3d)
- `--primary-light`: Light green (#3d7a52)
- `--accent`: Gold/accent color (#c9a227)

### Content
All text content can be edited directly in `index.php`

## License

This website is created for PineCrest and is proprietary.
