# SARPA (Snake Awareness, Reporting & Protection App)

SARPA is a PHP & MySQL-based web platform designed to empower citizens to report snake sightings and ensure timely intervention by local snake handlers. It also provides analytics and educational support using AI image recognition.

---

## 🚀 Features

### ✅ User Features

- User registration and login with session handling
- OTP verification with expiry and resend logic
- "Remember Me" functionality
- Submit snake sighting reports with:
  - Address fields (district, city, postcode, etc.)
  - Optional snake description and image
- Complaint summary with:
  - Unique complaint ID
  - Gemini AI image analysis integration
  - Copy-to-clipboard for complaint sharing
- User dashboard:
  - View past sighting history
  - Update personal and contact details
  - Reset password
  - Activity log with user actions
  - Tailwind CSS and Driver.js walkthrough
  - Dark mode support

### ✅ Partner Features (In Progress)

- Dedicated dashboard to view complaints within partner's district
- Accept/lock system: first responder locks the complaint
- Real-time update support (AJAX/WebSocket)

### ✅ Admin Tools (Planned)

- Google OAuth2 login
- User management, district control, and analytics
- Export CSV for complaints

---

## 🛠️ Tech Stack

- **Frontend**: HTML5, Tailwind CSS, JavaScript (Vanilla + Driver.js)
- **Backend**: PHP (Procedural), MySQLi
- **Database**: MySQL (cPanel/Linux compatible)
- **AI Integration**: Gemini Pro Vision for image analysis
- **Hosting**: Compatible with shared cPanel hosting or local Herd setup

---

## 📁 Directory Structure

```plaintext
/
├── index.php
├── login.php
├── register.php
├── otp-verify.php
├── user-dashboard.php
├── partner-dashboard.php
├── complaint-summary.php
├── submit-sighting.php
├── update_profile.php
├── export-csv.php
├── js/
│   └── walkthrough.js
├── css/
│   └── style.css
├── includes/
│   ├── db.php
│   └── functions.php
└── README.md
```
