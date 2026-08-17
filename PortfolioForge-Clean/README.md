# Portfolio Forge

**Portfolio Forge** is a dynamic, database-driven web-based portfolio builder designed for students and professionals across IT and non-IT academic/professional backgrounds. It enables users to create, edit, customize, and publish a professional portfolio website without requiring programming knowledge.

---

## ≡ƒîƒ Features

- **User Authentication & Session Management**: Secure user registration, password hashing (`password_hash()`), login with username/email, and account profile management.
- **Single Portfolio per User**: Strict 1:1 relationship between registered user and portfolio.
- **Non-AI Resume Extraction**: Upload PDF resumes and automatically extract readable text (via `pdftotext` with regex fallback) to pre-fill portfolio sections.
- **Fully Editable Data**: Manual and extracted data become standard editable portfolio fields after saving. Users can add missing data, edit existing details, or delete inaccurate information.
- **Five General-Purpose Templates**: Modern, Minimal, Professional, Creative, and Classic layouts differentiated purely by visual presentation.
- **Seamless Template Switching**: Switch between templates dynamically without losing any portfolio data or uploaded resumes.
- **Flexible Sections Management**: Support for core sections (About, Education, Skills, Projects, Experience, Contact) and optional sections (Certifications, Achievements, Languages, Activities, Interests) with customizable visibility and display ordering.
- **Personalized Portfolio URLs**: Public access via clean slug routes (e.g. `/portfolio/john-doe`).
- **Portfolio View Analytics**: Real-time view tracking (`portfolio_visits`) for public visits while excluding owner previews.
- **Administrator Portal**: Admin dashboard to manage user account activation/deactivation (preserving data), template availability, and view system metrics. Admins cannot edit user portfolio content.

---

## ≡ƒ¢á∩╕Å Technology Stack

- **Frontend**: HTML5, CSS3 (Custom CSS, strictly no Bootstrap or external CSS frameworks), Vanilla JavaScript
- **Backend**: PHP 8+
- **Database**: MySQL / MariaDB
- **Development Environment**: XAMPP / WAMP / LAMP

---

## ≡ƒôü Folder Structure

```
/portfolio-forge
Γöé
Γö£ΓöÇΓöÇ /admin
Γöé   Γö£ΓöÇΓöÇ dashboard.php      # Admin analytics dashboard
Γöé   Γö£ΓöÇΓöÇ login.php          # Admin authentication
Γöé   Γö£ΓöÇΓöÇ logout.php         # Admin session destruction
Γöé   Γö£ΓöÇΓöÇ templates.php      # Admin template manager
Γöé   ΓööΓöÇΓöÇ users.php          # Admin user account management
Γöé
Γö£ΓöÇΓöÇ /assets
Γöé   Γö£ΓöÇΓöÇ /css
Γöé   Γöé   Γö£ΓöÇΓöÇ dashboard.css  # Dashboard layouts and tables
Γöé   Γöé   Γö£ΓöÇΓöÇ style.css      # Core global styles and navbar
Γöé   Γöé   ΓööΓöÇΓöÇ templates.css  # Portfolio templates styles
Γöé   ΓööΓöÇΓöÇ /images
Γöé
Γö£ΓöÇΓöÇ /config
Γöé   ΓööΓöÇΓöÇ database.php       # PDO MySQL database connection
Γöé
Γö£ΓöÇΓöÇ /includes
Γöé   Γö£ΓöÇΓöÇ admin-auth.php     # Admin session guard
Γöé   Γö£ΓöÇΓöÇ auth.php           # User session guard & active account check
Γöé   Γö£ΓöÇΓöÇ footer.php         # Layout footer
Γöé   Γö£ΓöÇΓöÇ functions.php      # PDF parser, file uploads, slug generator, CSRF
Γöé   ΓööΓöÇΓöÇ header.php         # Layout header & flash alerts
Γöé
Γö£ΓöÇΓöÇ /portfolio
Γöé   ΓööΓöÇΓöÇ view.php           # Public portfolio router & view recorder
Γöé
Γö£ΓöÇΓöÇ /templates
Γöé   Γö£ΓöÇΓöÇ /classic           # Classic layout renderer
Γöé   Γö£ΓöÇΓöÇ /creative          # Creative layout renderer
Γöé   Γö£ΓöÇΓöÇ /minimal           # Minimal layout renderer
Γöé   Γö£ΓöÇΓöÇ /modern            # Modern layout renderer
Γöé   ΓööΓöÇΓöÇ /professional      # Professional layout renderer
Γöé
Γö£ΓöÇΓöÇ /tests
Γöé   ΓööΓöÇΓöÇ verify_app.php     # Automated test suite
Γöé
Γö£ΓöÇΓöÇ /uploads
Γöé   Γö£ΓöÇΓöÇ /profiles          # Profile picture uploads
Γöé   ΓööΓöÇΓöÇ /resumes           # Resume PDF uploads
Γöé
Γö£ΓöÇΓöÇ .htaccess              # Clean URL rewriting
Γö£ΓöÇΓöÇ database.sql           # Schema DDL & seed data
Γö£ΓöÇΓöÇ index.php              # Public landing page
Γö£ΓöÇΓöÇ login.php              # User login page
Γö£ΓöÇΓöÇ logout.php             # User logout
Γö£ΓöÇΓöÇ register.php           # User registration page
ΓööΓöÇΓöÇ README.md              # Project documentation
```

---

## ≡ƒÜÇ Setup & Installation Instructions (XAMPP)

1. **Clone or Extract Project**:
   Place the project folder in your XAMPP `htdocs` directory (e.g., `C:/xampp/htdocs/portfolio-forge`).

2. **Database Configuration**:
   - Open **phpMyAdmin** (`http://localhost/phpmyadmin`).
   - Create a new database named `portfolio_forge`.
   - Import `database.sql` into `portfolio_forge`.
   - Alternatively, execute via MySQL command line:
     ```bash
     mysql -u root -p portfolio_forge < database.sql
     ```

3. **Database Connection Credentials**:
   Verify or update database credentials in `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'forge_user'); // or 'root'
   define('DB_PASS', 'forge_pass'); // or ''
   define('DB_NAME', 'portfolio_forge');
   ```

4. **Default Credentials**:
   - **Default Admin Account**:
     - **Username**: `admin`
     - **Password**: `admin123`

5. **Run Application**:
   - Start Apache and MySQL in XAMPP Control Panel.
   - Access the landing page at: `http://localhost/portfolio-forge/` or `http://localhost:8000/`.

---

## ≡ƒº¬ Testing

To run the automated backend test suite:

```bash
php tests/verify_app.php
```

The test suite automatically tests database schema integrity, user registration, password verification, portfolio 1:1 constraints, section CRUD, template switching without data loss, non-AI resume extraction parser, view count incrementation, and admin user deactivation logic.

---

## ≡ƒöÆ Security Measures

- **Password Protection**: Passwords securely hashed with `password_hash()` and verified using `password_verify()`.
- **Prepared Statements**: All database operations use PDO prepared statements to protect against SQL Injection.
- **CSRF Token Validation**: Form submissions utilize session-based CSRF tokens.
- **File Upload Security**: Strict MIME type validation, file extension checks, file size limits, and randomized filename generation.
- **Data Privacy & Isolation**: Users can only manage their own portfolio data. Deactivated users are blocked from logging in, and their published portfolios are automatically hidden.

