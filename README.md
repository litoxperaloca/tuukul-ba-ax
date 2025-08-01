# Tuukul Ba'ax - Thought in Action 

AI-Powered Educational Planning Platform

![Project Banner](https://tuukulbaax.ironplatform.com.uy/img/tuukulbaax.png)

**Tuukul Ba'ax** (from Mayan: *To Think, To Imagine*) is a dynamic web platform designed to revolutionize the educational planning process. It empowers educators by leveraging Artificial Intelligence to automatically generate detailed didactic plans based on curriculum content, saving valuable time and enhancing the quality of lesson preparation.

The platform is built on a custom PHP MVC framework, providing a robust and organized structure for managing educational institutions, study plans, courses, students, and more.

---

## 📋 Table of Contents

- [About The Project](#about-the-project)
- [✨ Key Features](#-key-features)
- [🛠️ Technology Stack](#️-technology-stack)
- [🏗️ Architecture](#️-architecture)
- [🚀 Getting Started](#-getting-started)
  - [Prerequisites](#prerequisites)
  - [Installation](#installation)
- [🔧 Usage](#-usage)
  - [User Roles](#user-roles)
  - [Creating an Admin User](#creating-an-admin-user)
- [🤝 Contributing](#-contributing)
- [📄 License](#-license)
- [📧 Contact](#-contact)

---

## 📖 About The Project

In today's fast-paced educational environment, teachers are often burdened with extensive administrative tasks, with lesson planning being one of the most time-consuming. **Tuukul Ba'ax** addresses this challenge by providing an intelligent assistant that automates the creation of didactic plans.

A teacher can simply select the curriculum content for their subject, and the integrated OpenAI assistant will generate a comprehensive, structured, and pedagogically sound lesson plan. This plan can then be reviewed, edited, and exported as a PDF.

The platform serves as a centralized hub for:
- **Administrators:** To manage the foundational data of the educational system, such as institutions, study programs, and user access.
- **Teachers:** To manage their groups, students, and most importantly, to generate and organize their lesson plans with AI assistance.
- **Students:** To view their group information and assigned materials (future scope).

---

## ✨ Key Features

- **🤖 AI-Powered Didactic Planning:** Automatically generates detailed lesson plans using OpenAI's API based on selected curriculum content.
- **🏛️ Educational Center Management:** Full CRUD (Create, Read, Update, Delete) functionality for educational centers.
- **📚 Curriculum Management:** Manage study plans, courses, subjects, and specific curriculum content.
- **👨‍🏫 Teacher Dashboard:** A dedicated space for teachers to manage their groups, view students, and create lesson plans.
- **👥 Group Management:** Teachers can create, edit, and view their student groups, assigning them to specific courses and educational centers.
- **👤 User Authentication & Roles:** Secure login system with distinct roles and permissions for Administrators, Teachers, and Students.
- **📄 PDF Export:** Export generated didactic plans to PDF format for easy printing and sharing, using the Dompdf library.
- **📝 Dynamic Form Builder:** A system for creating and managing dynamic forms for various data entry needs.
- **🌐 API Integration:** A dedicated API endpoint to handle requests to the OpenAI assistant for plan generation.

---

## 🛠️ Technology Stack

This project is built with a focus on simplicity and robustness, using the following technologies:

- **Backend:** **PHP 8.x** (Custom MVC Framework)
- **Database:** **Postgres SQL / MySQL / MariaDB**
- **Frontend:** **HTML5, CSS3, Vanilla JavaScript**
- **AI Integration:** **OpenAI API**
- **Libraries:**
  - [Dompdf](https://github.com/dompdf/dompdf): For generating PDF documents from HTML.
  - [FPDF](http://www.fpdf.org/): An alternative library for PDF creation.

---

## 🏗️ Architecture

The project follows a classic **Model-View-Controller (MVC)** architectural pattern to ensure a clear separation of concerns.

-   **`/app/models`**: Contains the data logic. Each file represents a database table and handles all interactions with it (e.g., `User.php`, `Planeacion.php`). `Database.php` manages the DB connection.
-   **`/app/views`**: Holds the presentation layer. These are the PHP files that render the HTML content for the user interface.
-   **`/app/controllers`**: Acts as the intermediary between Models and Views. It receives user requests, interacts with the model to fetch data, and passes that data to the appropriate view.
-   **`/public`**: The web server's document root. It contains the main entry point (`index.php`), assets (CSS, JS, images), and handles all incoming requests via its `.htaccess` file for URL routing.
-   **`/config`**: Contains configuration files, including database credentials and application constants (`config.php`) and the database schema (`database.sql`).
-   **`/app/lib`**: Contains external libraries like Dompdf and FPDF.

---

## 🚀 Getting Started

Follow these steps to set up a local development environment.

### Prerequisites

-   A local server environment (XAMPP, MAMP, LAMP, etc.)
-   PHP 8.0 or higher
-   MySQL or MariaDB database server. Supports option for Postgres
-   An OpenAI API Key

### Installation

1.  **Clone the repository:**
    ```sh
    git clone [https://github.com/your-username/tuukul-ba-ax.git](https://github.com/your-username/tuukul-ba-ax.git)
    cd tuukul-ba-ax
    ```

2.  **Database Setup:**
    -   Create a new database in your MySQL/MariaDB server (e.g., `tuukul_ba_ax_db`).
    -   Import the database schema from `config/database.sql`:
        ```sh
        mysql -u your_username -p your_database_name < config/database.sql
        ```

3.  **Configure the Application:**
    -   Navigate to the `config` directory.
    -   Rename `config.php.example` to `config.php` (if an example file is provided) or edit `config.php` directly.
    -   Update the database credentials:
        ```php
        define('DB_HOST', 'localhost');
        define('DB_USER', 'your_username');
        define('DB_PASS', 'your_password');
        define('DB_NAME', 'your_database_name');
        ```
    -   Add your OpenAI API Key:
        ```php
        define('OPENAI_API_KEY', 'your-openai-api-key');
        ```

4.  **Set up the Web Server:**
    -   Configure your local web server (e.g., Apache) to point the document root to the `/public` directory of the project.
    -   Ensure that `mod_rewrite` is enabled in your Apache configuration to allow for clean URLs.

5.  **Run Installation Scripts (Optional):**
    The repository includes several `install_phaseX.sh` scripts. Review these scripts to understand the permissions and setup steps they perform. You may need to run them if you encounter permission issues.
    ```sh
    chmod +x install.sh
    ./install.sh
    ```

---

## 🔧 Usage

Once the application is installed, you can access it through your local server's URL (e.g., `http://localhost/`).

### User Roles

1.  **Student:** Can register for an account. Has limited access.
2.  **Teacher:** Can be promoted from a student account by an admin. Can manage groups and generate didactic plans.
3.  **Admin:** Has full access to the system, including managing core data like educational centers, courses, and user roles.

### Creating an Admin User

By default, all new users are registered as "Student". To create an administrator:

1.  Register a new user through the application's registration form.
2.  Run the `promote_user.php` script from your command line, providing the email of the user you want to promote:
    ```sh
    php scripts/promote_user.php your-email@example.com
    ```
3.  This will elevate the user's role to "Admin".

---

## 🤝 Contributing

Contributions are what make the open-source community such an amazing place to learn, inspire, and create. Any contributions you make are **greatly appreciated**.

If you have a suggestion that would make this better, please fork the repo and create a pull request. You can also simply open an issue with the tag "enhancement".

1.  Fork the Project
2.  Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3.  Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4.  Push to the Branch (`git push origin feature/AmazingFeature`)
5.  Open a Pull Request

---

## 📄 License

This project is distributed under the MIT License. See `LICENSE` for more information.

---

## 📧 Contact

Pablo Pignolo & Lorena Anaya - pablo.pignolo@gmail.com - https://uy.linkedin.com/in/pablopignolo
Project Link: [https://github.com/litoxperaloca/tuukul-ba-ax](https://github.com/litoxperaloca/tuukul-ba-ax)

