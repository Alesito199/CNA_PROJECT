# CNA Upholstery Management System

A comprehensive business management system for CNA Upholstery with multi-language support, built with PHP 8.2+ and modern web technologies.

## Features

- **Multi-language Support**: English (default) and Spanish
- **Secure Authentication**: JWT-based with ARGON2ID password hashing
- **Client Management**: Complete CRUD operations with search and pagination
- **Estimate & Invoice System**: Professional document generation with tax calculations
- **Portfolio Gallery**: Before/after work showcase with AWS S3 integration
- **Responsive Design**: Built with Tailwind CSS for mobile-first experience
- **Security First**: CSRF protection, XSS prevention, rate limiting, and audit logging

## Technical Stack

- **Backend**: PHP 8.2+ with custom MVC framework
- **Database**: PostgreSQL with UUID primary keys
- **Frontend**: Tailwind CSS with custom components
- **File Storage**: AWS S3 integration for images
- **PDF Generation**: mPDF library for professional documents
- **Email**: PHPMailer for automated communications

## Quick Start

### Development Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/Alesito199/CNA_PROJECT.git
   cd CNA_PROJECT
   ```

2. **Install dependencies**
   ```bash
   composer install --no-dev
   npm install
   ```

3. **Build assets**
   ```bash
   npm run build
   ```

4. **Configure environment**
   ```bash
   cp .env.example .env
   # Edit .env with your configuration
   ```

5. **Set up database**
   ```bash
   # Create PostgreSQL database and run setup.sql
   psql -U your_user -d your_database -f setup.sql
   ```

6. **Start development server**
   ```bash
   php server.php 8000
   # Visit http://localhost:8000
   ```

### Default Login

- **Username**: admin
- **Password**: admin123 (Change in production!)

## Project Structure

```
CNA_PROJECT/
├── public/                 # Web-accessible files
│   ├── index.php          # Application entry point
│   └── assets/            # CSS, JS, images
├── src/                   # Application source code
│   ├── Controllers/       # MVC Controllers
│   ├── Models/           # Database models
│   ├── Views/            # HTML templates
│   ├── Utils/            # Utility classes
│   ├── Config/           # Configuration
│   └── Languages/        # Translation files
├── database/             # Database migrations
├── storage/              # Logs and cache
└── uploads/              # Temporary file uploads
```

## Configuration

### Environment Variables

Key configuration options in `.env`:

```bash
# Application
APP_NAME="CNA Upholstery Management System"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_HOST=localhost
DB_DATABASE=cna_upholstery
DB_USERNAME=your_username
DB_PASSWORD=your_password

# AWS S3
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_BUCKET=your-bucket-name

# Business Settings
BUSINESS_NAME="CNA Upholstery"
TAX_RATE=6.625
```

### Database Setup

The system uses PostgreSQL with the following key tables:
- `users` - System users and authentication
- `clients` - Customer information
- `estimates` - Quote management
- `invoices` - Billing and payments
- `portfolio_items` - Work showcase
- `security_logs` - Audit trail

## Features Overview

### Client Management
- Add, edit, delete, and search clients
- Company and personal client support
- Client statistics and history
- Relationship tracking with estimates/invoices

### Estimate System
- Professional estimate creation
- Line item management
- Automatic tax calculations
- Status tracking (draft, sent, approved, etc.)
- PDF generation and email delivery

### Invoice System
- Convert estimates to invoices
- Payment tracking and recording
- Overdue invoice management
- Revenue reporting

### Security Features
- JWT token authentication
- ARGON2ID password hashing
- CSRF token protection
- XSS prevention
- SQL injection protection
- Rate limiting
- Security event logging

### Multi-language Support
- English and Spanish translations
- Dynamic language switching
- Localized number/date formatting
- Currency formatting

## Production Deployment

### Requirements
- PHP 8.2+ with extensions: pdo_pgsql, gd, curl, mbstring
- PostgreSQL 12+
- Nginx or Apache web server
- SSL certificate

### Web Server Configuration

#### Nginx
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/cna-project/public;
    
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

#### Apache
The included `.htaccess` files provide proper URL rewriting.

### Security Checklist
- [ ] Change default admin credentials
- [ ] Set strong `APP_KEY` and `JWT_SECRET`
- [ ] Configure proper file permissions (755 for directories, 644 for files)
- [ ] Enable HTTPS with valid SSL certificate
- [ ] Configure firewall rules
- [ ] Set up regular database backups
- [ ] Review and configure rate limiting
- [ ] Set up log monitoring

## Development

### Adding New Features
1. Create model in `src/Models/`
2. Create controller in `src/Controllers/`
3. Add routes in `src/routes.php`
4. Create views in `src/Views/`
5. Update language files if needed

### Coding Standards
- PSR-4 autoloading
- PSR-12 coding standards
- Type declarations for all methods
- Comprehensive error handling
- Security-first approach

## License

This project is proprietary software developed for CNA Upholstery. All rights reserved.

## Support

For technical support or questions about this system, please contact the development team.
