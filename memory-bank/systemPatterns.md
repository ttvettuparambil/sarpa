# System Patterns: SARPA

## Architecture Overview

SARPA follows a traditional PHP-based web application architecture with the following components:

1. **Frontend Layer**

   - HTML templates with PHP includes for reusable components
   - Tailwind CSS for styling with responsive design
   - JavaScript for client-side interactivity and form validation
   - Chart.js for data visualization
   - Video.js for educational video playback
   - Driver.js for interactive guided tours
   - Dark mode support with localStorage preference

2. **Backend Layer**

   - PHP for server-side processing and business logic
   - MySQL database for data storage
   - Session-based authentication with OTP verification
   - AJAX endpoints for asynchronous data operations
   - Middleware pattern for cross-cutting concerns (e.g., maintenance mode)

3. **File Storage**
   - Local file system for storing uploaded images
   - Organized directory structure for different types of uploads

4. **System Features**
   - Maintenance mode toggle (super_admin only)
   - Site-wide settings stored in database
   - Role-based access control (user, partner, super_admin)

## Component Structure

The application uses a component-based approach for the frontend, with reusable elements:

```
components/
  ├── header.php      # Site navigation and branding
  ├── footer.php      # Copyright, links, contact info
  ├── alerts.php      # For displaying success/error messages
  └── admin-sidebar.php # Admin navigation sidebar
```

## Middleware Structure

The application implements middleware patterns for cross-cutting concerns:

```
├── maintenance_check.php  # Checks if site is in maintenance mode
└── maintenance_page.php   # Displayed when site is in maintenance mode
```

## Database Structure

Key tables in the database include:

```
├── users                  # User accounts and authentication
├── user_profiles          # Extended user information
├── snake_sightings        # Reported snake sightings
├── password_resets        # Password reset tokens
├── login_attempts         # Track failed login attempts
├── user_remember_tokens   # Remember-me functionality
├── site_settings          # Site-wide configuration settings
└── account_activity       # User activity logging
```

These components are included in each page template to maintain consistency and reduce duplication.

## Database Schema

The database follows a relational model with the following key tables:

1. **users**

   - User account information and authentication details
   - Profile information including contact details

2. **snake_sightings**

   - Detailed information about reported snake sightings
   - References to uploaded images and location data

3. **account_activity**

   - Audit trail of user actions and system events
   - Used for security monitoring and user activity tracking

4. **login_attempts**

   - Tracks failed login attempts for security purposes
   - Used to implement account lockout after multiple failures

5. **user_video_progress**
   - Tracks user progress in educational videos
   - Stores timestamp where user left off for each video
   - Enables resume functionality for educational content

## Authentication Flow

1. **Registration**

   - User provides email and personal information
   - System validates input and creates account
   - Email verification through OTP

2. **Login**

   - User enters email and password
   - System validates credentials
   - If valid, OTP is generated and sent to user
   - User enters OTP for final verification
   - Session is created with appropriate timeout

3. **Session Management**
   - Sessions expire after 10 minutes of inactivity
   - Warning shown 2 minutes before expiration
   - Option to extend session without re-authentication
   - Activity logging for security purposes

## Design Patterns

1. **Component Inclusion Pattern**

   - Reusable UI components through PHP includes
   - Consistent header, footer, and alert messaging

2. **Form Validation Pattern**

   - Server-side validation for all form submissions
   - Client-side validation for immediate feedback
   - Consistent error messaging and display

3. **Data Access Pattern**

   - Prepared statements for all database operations
   - Parameterized queries to prevent SQL injection
   - Consistent error handling and logging

4. **Authentication Pattern**

   - Multi-factor authentication with OTP
   - Session-based authentication with secure cookies
   - Progressive security measures (lockouts after failed attempts)

5. **Responsive Design Pattern**

   - Mobile-first approach with Tailwind CSS
   - Fluid layouts that adapt to different screen sizes
   - Consistent user experience across devices

6. **Dark Mode Pattern**

   - User preference-based theme switching
   - Persistent theme selection using localStorage
   - Consistent color scheme across light and dark modes

7. **Video Progress Tracking Pattern**

   - Automatic saving of video playback position
   - Interval-based progress updates to minimize server requests
   - Resume functionality for seamless user experience
   - Event-based tracking (play, pause, end) for accurate progress data

8. **User Onboarding Pattern**
   - Interactive guided tours for new users
   - Step-by-step introduction to interface elements
   - First-time user detection with localStorage
   - Persistent tour availability for reference

## Error Handling

1. **User-Facing Errors**

   - Friendly error messages through the alerts component
   - Contextual guidance for resolving issues
   - Form validation errors displayed inline

2. **System Errors**
   - Detailed logging for debugging purposes
   - Graceful degradation when services are unavailable
   - Appropriate HTTP status codes for API responses

## Performance Considerations

1. **Image Optimization**

   - Compression of uploaded images
   - Appropriate image formats for web display
   - Lazy loading for image-heavy pages

2. **Video Optimization**

   - YouTube embedding for bandwidth-efficient video delivery
   - Progressive loading for video content
   - Adaptive quality based on user's connection speed

3. **Database Optimization**

   - Indexed fields for common queries
   - Optimized query patterns for dashboard statistics
   - Connection pooling for efficient resource usage

4. **Frontend Optimization**
   - Minimal JavaScript with focused functionality
   - Efficient DOM manipulation
   - Responsive image loading based on device capabilities
   - Throttled event handlers for performance-intensive operations
