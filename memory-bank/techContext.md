# Technical Context: SARPA

## Technology Stack

### Frontend

- **HTML5**: Semantic markup for page structure
- **Tailwind CSS**: Utility-first CSS framework for styling
- **JavaScript**: Client-side interactivity and validation
- **Chart.js**: Data visualization library for statistics
- **DataTables**: Enhanced table functionality with sorting and pagination
- **Flatpickr**: Date and time picker for form inputs

### Backend

- **PHP**: Server-side scripting language (version 7.4+)
- **MySQL**: Relational database management system
- **Apache/Nginx**: Web server

### Development Tools

- **Git**: Version control
- **VSCode**: Code editor
- **Chrome DevTools**: Frontend debugging and optimization

## Key Dependencies

| Dependency   | Version | Purpose                                 |
| ------------ | ------- | --------------------------------------- |
| Tailwind CSS | 3.x     | Utility-first CSS framework             |
| Chart.js     | 3.x     | Data visualization                      |
| jQuery       | 3.6.0   | JavaScript library for DOM manipulation |
| DataTables   | 1.11.5  | Enhanced table functionality            |
| Flatpickr    | Latest  | Date/time picker                        |

## Environment Setup

### Local Development

- LAMP/WAMP/MAMP stack for local development
- Database setup with initial schema and seed data
- Environment variables for configuration

### Production

- Shared hosting or VPS with PHP support
- MySQL database with optimized configuration
- SSL certificate for secure connections

## File Structure

```
sarpa/
├── components/           # Reusable UI components
│   ├── header.php
│   ├── footer.php
│   └── alerts.php
├── uploads/              # User-uploaded files
│   └── [date-based directories]
├── profile_pics/         # User profile pictures
├── memory-bank/          # Project documentation
├── *.php                 # Main application files
├── script.js             # Global JavaScript
└── style.css             # Legacy CSS (being replaced by Tailwind)
```

## Database Structure

### Key Tables

#### users

- `id`: Primary key
- `email`: User email (unique)
- `password`: Hashed password
- `first_name`, `last_name`: User's name
- `phone`: Contact number
- `role`: User role (user, partner, super_admin)
- `district`, `city`, etc.: Address information
- Timestamps for creation and updates

#### snake_sightings

- `id`: Primary key
- `complaint_id`: Public-facing ID for the sighting
- `user_email`: Foreign key to users
- `district`, `city`, etc.: Location information
- `datetime`: When the snake was sighted
- `description`: User-provided description
- `image_path`: Path to uploaded image
- Timestamps for creation and updates

#### account_activity

- `id`: Primary key
- `user_id`: Foreign key to users
- `action_type`: Type of activity (LOGIN, SNAKE_SIGHTING_STARTED, etc.)
- `action_description`: Detailed description
- `ip_address`: User's IP address
- `browser`, `device_type`: User agent information
- Timestamp for the activity

#### login_attempts

- `id`: Primary key
- `email`: User email
- `ip_address`: User's IP address
- `attempt_time`: When the attempt occurred
- `unlock_time`: When the lockout expires (if applicable)

## Authentication System

### Security Features

- Password hashing using PHP's `password_hash()` function
- OTP verification for login
- Account lockout after multiple failed attempts
- Session timeout after inactivity
- CSRF protection for forms
- Prepared statements for all database queries

### Session Management

- PHP sessions with secure configuration
- 10-minute inactivity timeout
- Warning 2 minutes before timeout
- Session extension without re-authentication
- Activity logging for security audit

## API Endpoints

The application primarily uses server-rendered pages rather than APIs, but includes a few AJAX endpoints:

- `extend_session.php`: Extends the user's session
- `get_sighting_stats.php`: Retrieves statistics for charts
- `resend_otp.php`: Resends OTP for verification

## Development Constraints

- Must maintain backward compatibility with existing data
- Must work on shared hosting environments
- Must support older browsers (IE11+)
- Must be optimized for mobile devices with limited bandwidth
- Must handle intermittent connectivity gracefully

## Performance Considerations

- Image optimization for uploads
- Efficient database queries with proper indexing
- Minimal JavaScript with focused functionality
- Responsive design for all screen sizes
- Caching strategies for static content
