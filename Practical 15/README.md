# StudentHub – Student Management Portal

## 1. Project Overview

StudentHub is a web-based student management portal developed to simplify student information management, event registration, feedback collection, and administrative operations. It provides separate access for students and administrators through authentication and role-based access control.

The project integrates frontend technologies with PHP and MySQL to provide a dynamic and interactive user experience.

## 2. Objectives

- Develop a centralized portal for student-related activities.
- Implement secure user registration and login.
- Manage student records and events using CRUD operations.
- Allow students to register for events and submit feedback.
- Display dynamic data using the Fetch API and JSON.
- Provide an administrative dashboard with statistics and audit logging.
- Test, document, and deploy the application locally using XAMPP and Apache.

## 3. Technologies Used

| Technology | Purpose |
|---|---|
| HTML5 | Website structure |
| CSS3 | Styling and responsive layout |
| JavaScript | Client-side interactivity and validation |
| PHP | Server-side processing and authentication |
| MySQL | Database management |
| Fetch API and JSON | Asynchronous data communication |
| XAMPP | Local development environment |
| Apache | Web server |
| Git and GitHub | Version control and project submission |

## 4. Features

### User Authentication
- Student registration and login
- Password hashing and verification
- Session management and logout
- Role-based access control

### Student Management
- Add, view, update, and delete student records
- Search and filter student information

### Event Management
- Create, view, update, and delete events
- Upload event posters with file validation
- Manage event registration records

### Feedback Management
- Submit and store student feedback
- Retrieve feedback records for administrative review

### Dynamic Data and AJAX
- Retrieve and display JSON data using the Fetch API
- Perform supported asynchronous operations without a full-page reload
- Implement dynamic rendering and any available search, filtering, sorting, or pagination features

### Admin Dashboard
- Display student, event, registration, and other available statistics
- View recent registrations
- Display recorded audit activities
- Restrict administrative functionality to authorized users

## 5. Project Structure

The project is organized into separate folders for pages available before and after login, along with PHP backend and database files.

```text
StudentHub/
├── Before Login/
│   ├── Homepage.html
│   ├── login.html
│   ├── Registration.html
│   ├── style.css
│   └── JavaScript files
├── After Login/
│   ├── Dashboard.html
│   ├── admin_dashboard.php
│   ├── student management files
│   ├── event management files
│   └── other module files
├── db.php
├── setup.sql
├── login_user.php
├── logout.php
├── README.md
└── Other project files
```

*Note: Adjust the file and folder names in this section to match the actual project structure.*

## 6. Software Requirements

- Windows operating system
- XAMPP with Apache and MySQL
- PHP supported by the installed XAMPP version
- Web browser such as Google Chrome
- Visual Studio Code or another code editor
- Git and a GitHub account

## 7. Installation and Setup

### Step 1: Install and Start XAMPP

1. Install XAMPP on the system.
2. Open the XAMPP Control Panel.
3. Start the Apache and MySQL services.

### Step 2: Copy the Project

1. Copy the StudentHub project folder into the XAMPP `htdocs` directory.
2. Ensure the project files are placed in the correct folders.

Example path:

```text
C:\xampp\htdocs\StudentHub
```

### Step 3: Configure the Database

1. Open `http://localhost/phpmyadmin/` in the browser.
2. Create the database named `studenthub_db`, if it does not already exist.
3. Import the provided SQL setup file using phpMyAdmin, or execute its SQL statements.
4. Check that the required tables have been created.
5. Verify the database connection settings in `db.php`.

Do not import the SQL file blindly into a database containing important existing data. Back up the database first.

### Step 4: Run the Application

Open the project in your browser using its configured entry page.

Example:

```text
http://localhost/StudentHub/
```

If the application uses a different entry file or folder, open the corresponding URL.

### Step 5: Test the Application

- Test registration and login.
- Verify student and administrator permissions.
- Test student and event CRUD operations.
- Submit feedback.
- Verify Fetch API and AJAX functionality.
- Test logout and protected-page access.
- Check the website at different screen widths.

## 8. Testing

The following test cases should be executed before final submission.

| Test Case | Expected Result |
|---|---|
| Valid login | User accesses the appropriate dashboard |
| Invalid login | An error message is displayed |
| Duplicate registration | Duplicate account creation is rejected |
| Unauthorized admin access | Access is denied or redirected |
| Student CRUD operations | Student records are updated correctly |
| Event CRUD operations | Event records are updated correctly |
| Feedback submission | Feedback is stored successfully |
| Fetch API request | JSON data is retrieved and displayed |
| Logout | Session is ended and protected access is restricted |
| Responsive layout | Pages remain usable at different screen widths |

Actual test results should be recorded after testing the application.

## 9. Security Measures

The project incorporates security measures such as password hashing and verification, session-based authentication, role-based access control, server-side input validation, and database query protection.

File uploads should be validated for permitted file types and sizes. Sensitive database credentials should not be published in the public GitHub repository.

## 10. Local Deployment

StudentHub is intended to run locally using the XAMPP Apache web server and MySQL database server.

The application can be accessed through localhost. If required, an Apache virtual host can be configured to access the application through a custom local hostname.

A local virtual host does not make the application publicly available on the internet.

## 11. GitHub Submission

To submit the project:

1. Create a GitHub repository named `StudentHub`.
2. Add the project source files and database setup file.
3. Include this README documentation.
4. Add a `.gitignore` file for unnecessary files and sensitive configuration.
5. Commit and push the project to GitHub.
6. Copy the repository URL into the practical submission.

Before uploading, verify that no passwords, private credentials, or other sensitive information are included.

**GitHub Repository:** Add your repository URL here.

## 12. Conclusion

StudentHub integrates student management, authentication, event management, feedback, dynamic data handling, and administrative features into a single web application. The project demonstrates the use of HTML, CSS, JavaScript, PHP, MySQL, and Fetch API for developing a dynamic web portal. Final integration, testing, documentation, version control, and local deployment help ensure that the application is organized and ready for academic evaluation.
