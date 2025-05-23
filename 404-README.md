# Simple PHP Routing & 404 System - No .htaccess Required

This implementation provides a simple routing and 404 error handling system integrated directly into `index.php`, without requiring any server configuration or .htaccess files.

## How It Works

### Files Created:

1. **`index.php`** - Contains the routing logic and serves as the main entry point
2. **`404.php`** - The custom 404 error page with SARPA branding

### Implementation:

The system works by adding routing logic to the top of `index.php` that:

1. Checks for a `page` parameter in the URL
2. Maps page names to actual PHP files
3. Includes the appropriate file or shows 404 page
4. If no page parameter, shows the normal index.php content

### URL Structure:

**Routed URLs:**

- `index.php` - Home page (default)
- `index.php?page=login` - Login page
- `index.php?page=dashboard` - User dashboard
- `index.php?page=admin/dashboard` - Admin dashboard
- `index.php?page=submit-contact` - Form submissions

**Direct URLs (still work):**

- `login.php` - Direct access still works
- `register.php` - Direct access still works
- Other PHP files work as before

### Usage:

**For new links, use the query parameter format:**

```html
<a href="index.php?page=dashboard">Dashboard</a>
<a href="index.php?page=login">Login</a>
```

**For forms:**

```html
<form action="index.php?page=submit-contact" method="POST"></form>
```

### Testing:

1. **Valid page**: Access `index.php?page=dashboard` - works normally
2. **Invalid page**: Access `index.php?page=nonexistent` - shows 404 page
3. **Direct file access**: Access `login.php` - works (backward compatibility)
4. **Home page**: Access `index.php` - shows normal home page

### Benefits:

- ✅ No server configuration required (completely .htaccess-free)
- ✅ Works on any hosting environment
- ✅ True 404 handling for invalid page parameters
- ✅ Simple and easy to understand
- ✅ Backward compatibility with existing direct file access
- ✅ Consistent SARPA branding on 404 page
- ✅ Proper HTTP 404 status codes
- ✅ Easy to maintain and extend

### Adding New Routes:

To add a new route:

1. Add the route to the `$routes` array in `index.php`:

```php
'new-page' => 'new-page.php',
```

2. Create your `new-page.php` file

3. Link to it using `index.php?page=new-page`

### Route Categories:

- **Main pages**: `login`, `register`, `logout`, etc.
- **User pages**: `dashboard`, `profile`, `snake-sighting`, etc.
- **Admin pages**: `admin/dashboard`, `admin/users`, etc.
- **API endpoints**: `submit-contact`, `get-user-details`, etc.

### Security:

The system provides:

- Controlled access through route definitions
- 404 responses for undefined page parameters
- File existence validation before inclusion
- Backward compatibility for direct file access
- Protection against unauthorized file inclusion

This provides a simple but effective routing system with proper 404 handling that works entirely in PHP without any server configuration requirements.
