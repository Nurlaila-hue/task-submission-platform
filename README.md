# Task Submission Platform

## Overview
The Task Submission Platform is a web application designed for students and lecturers to manage task submissions efficiently. It provides a user-friendly interface for both user types, allowing them to interact with the system seamlessly.

## Features
- **User Authentication**: Secure login and registration for students and lecturers.
- **Student Dashboard**: 
  - Welcome message
  - Profile management (change username and password)
  - Task list with deadlines for uploading and deleting assignments
- **Lecturer Dashboard**: 
  - Profile management (change username and password)
  - Assign new tasks
  - View submissions from students
- **Responsive Design**: Visually appealing layout that adapts to different screen sizes.

## Project Structure
```
task-submission-platform
├── public
│   ├── index.php
│   ├── login.php
│   ├── register.php
│   ├── student_dashboard.php
│   ├── lecturer_dashboard.php
│   ├── assets
│   │   ├── css
│   │   │   └── style.css
│   │   └── js
│   │       └── main.js
├── src
│   ├── config
│   │   └── database.php
│   ├── controllers
│   │   ├── AuthController.php
│   │   ├── StudentController.php
│   │   └── LecturerController.php
│   ├── models
│   │   ├── User.php
│   │   ├── Task.php
│   │   └── Submission.php
│   ├── views
│   │   ├── partials
│   │   │   ├── header.php
│   │   │   └── footer.php
│   │   ├── student
│   │   │   ├── dashboard.php
│   │   │   └── profile.php
│   │   └── lecturer
│   │       ├── dashboard.php
│   │       └── profile.php
│   └── helpers
│       └── auth.php
├── sql
│   └── schema.sql
├── .gitignore
└── README.md
```

## Setup Instructions
1. **Clone the Repository**: 
   ```
   git clone <repository-url>
   ```
2. **Database Setup**:
   - Create a MySQL database and import the `sql/schema.sql` file to set up the necessary tables.
3. **Configuration**:
   - Update the database connection settings in `src/config/database.php`.
4. **Run the Application**:
   - Use a local server (like XAMPP or MAMP) to serve the `public` directory.
   - Access the application via `http://localhost/task-submission-platform/public`.

## Usage Guidelines
- Register as a new user to access the platform.
- Students can view and manage their tasks, while lecturers can assign tasks and review submissions.
- Ensure to log out after use for security purposes.

## Contributing
Contributions are welcome! Please submit a pull request or open an issue for any enhancements or bug fixes.

## License
This project is licensed under the MIT License.