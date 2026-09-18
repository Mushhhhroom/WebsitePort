-- ==========================================================
-- Database Schema & Seed Data for Jairus John Valdez Portfolio
-- Compatible with MySQL 5.7+ / 8.0+ / MariaDB
-- ==========================================================

CREATE DATABASE IF NOT EXISTS `portfolio` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `portfolio`;

-- 1. Users Table (Admin authentication)
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL DEFAULT 'Jairus John Valdez',
    `headline` VARCHAR(150) NOT NULL DEFAULT 'Computer Science Student & Systems Developer',
    `bio` TEXT NULL,
    `github` VARCHAR(255) DEFAULT 'https://github.com',
    `linkedin` VARCHAR(255) DEFAULT 'https://linkedin.com',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Resume Table (Dynamic resume sections and contents)
CREATE TABLE IF NOT EXISTS `resume` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `section` VARCHAR(50) NOT NULL, -- e.g. 'education', 'skills', 'experience', 'certifications'
    `title` VARCHAR(150) NOT NULL,
    `subtitle` VARCHAR(150) DEFAULT NULL,
    `date_range` VARCHAR(100) DEFAULT NULL,
    `content` TEXT NOT NULL,
    `display_order` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Projects Table (Project showcase)
CREATE TABLE IF NOT EXISTS `projects` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(150) NOT NULL,
    `description` TEXT NOT NULL,
    `tech_stack` VARCHAR(255) NOT NULL,
    `category` VARCHAR(50) NOT NULL DEFAULT 'Web Application',
    `github_link` VARCHAR(255) DEFAULT NULL,
    `demo_link` VARCHAR(255) DEFAULT NULL,
    `featured` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Messages Table (Secure contact form inquiries)
CREATE TABLE IF NOT EXISTS `messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `subject` VARCHAR(150) NOT NULL DEFAULT 'Portfolio Contact Request',
    `message` TEXT NOT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- SEED INITIAL DATA
-- Default Admin credentials:
-- Username: admin
-- Password: password123 (Hashed using bcrypt)
-- ==========================================================

-- Insert Default Admin User
INSERT INTO `users` (`username`, `password`, `email`, `full_name`, `headline`, `bio`, `github`, `linkedin`)
VALUES (
    'admin',
    '$2y$12$22F/nHX5b6TjWKj.uaRSQOSSwETvYSh9p08odPHYpMNKZ5ZadF3A6', -- verified bcrypt hash for 'password123'
    'Valdez.jairusjohn.deleste@gmail.com',
    'Jairus John D. Valdez',
    'Fourth-Year BS Computer Science Student | Website, Application & Mobile Developer',
    'Highly motivated Fourth-Year Bachelor of Science in Computer Science student at Quezon City University. Possesses a strong technical foundation in system troubleshooting and digital platforms, combined with a professional approach to problem-solving. Committed to delivering clear, efficient, and courteous support to users while maintaining high standards of service excellence in a fast-paced environment.',
    'https://github.com/Mushhhhroom',
    'https://www.linkedin.com/in/jairus-valdez-19469a313/'
)
ON DUPLICATE KEY UPDATE 
    `password` = VALUES(`password`), 
    `email` = VALUES(`email`), 
    `full_name` = VALUES(`full_name`), 
    `headline` = VALUES(`headline`), 
    `bio` = VALUES(`bio`), 
    `github` = VALUES(`github`), 
    `linkedin` = VALUES(`linkedin`);

-- Insert Accurate Resume Data
INSERT INTO `resume` (`section`, `title`, `subtitle`, `date_range`, `content`, `display_order`) VALUES
('education', 'Bachelor of Science in Computer Science', 'Quezon City University', '2023 - Present | Expected Graduation: 2027', 'Fourth-Year Bachelor of Science in Computer Science student. Possesses a strong technical foundation in system troubleshooting and digital platforms, software engineering, and database management.', 1),
('education', 'Senior High School — ICT Strand | With Honors, Grade 11', 'Young Achievers\' School of Caloocan INC.', 'S.Y 2020 - 2023', 'Information and Communications Technology (ICT) strand. Graduated with academic honors in Grade 11, developing core foundations in programming and computer systems.', 2),
('education', 'High School', 'Deparo High School, Caloocan City', 'S.Y 2016 - 2020', 'Completed secondary junior high school education with strong foundational academic background.', 3),
('experience', 'Junior Philippine Computer Society — QCU Chapter', 'Board of Programmer Officer', 'Active', '• Supported programming-related activities and student organization initiatives.\n• Collaborated with fellow officers to coordinate technical projects and events.\n• Helped promote programming and technology activities among Computer Science students.', 1),
('skills', 'Development', 'Core Development Roles', 'Proficient', 'Website, Application and Mobile Developer', 1),
('skills', 'Databases', 'Relational & NoSQL Stores', 'Advanced', 'MySQL, MongoDB, PostgreSQL, Supabase', 2),
('skills', 'Networking', 'Infrastructure & Systems', 'Technical', 'Network Troubleshooting, Cisco Router Configuration', 3),
('skills', 'Soft Skills', 'Professional Competencies', 'Key Strengths', 'Communication, Critical Thinking, Problem-Solving, Teamwork', 4),
('certifications', 'AWS Cloud Engineering Seminar', 'JPCS QCU Chapter', '2024', 'Exploration of cloud architecture, scalable engineering, and AWS cloud ecosystem fundamentals.', 1),
('certifications', 'Data Visualization Workshop', 'Zuitt', '2025', 'Hands-on workshop covering modern data visualization patterns, chart architectures, and data storytelling.', 2),
('certifications', 'IT Summit', 'Industry Conference', '2025', 'Attended sessions on emerging technologies, enterprise systems, and the future of computing.', 3),
('certifications', 'GLOBE x JPCS: Innovania', 'Innovation Summit & Workshop', '2025', 'Participated in collaborative technology innovation, problem-solving workshops, and digital transformation discussions.', 4);

-- Insert Accurate Projects Data
INSERT INTO `projects` (`title`, `description`, `tech_stack`, `category`, `github_link`, `demo_link`, `featured`) VALUES
('Loris Cafe — Point of Sale (POS) & Inventory System', 'Software Engineering Project at Quezon City University (S.Y 2025 – 2026). Loris Taste Cafe is a full-stack POS and inventory management application designed to streamline cafe operations through a modernized user interface with dedicated, toggleable modules separating front-end retail product management from back-end raw materials tracking. Full-Stack Development: Engineered both front-end mechanics and back-end logic for the POS system (Mobile) and inventory management (Desktop).', 'Java, JavaFX, MySQL, PostgreSQL, Supabase, Kotlin, Android Studio, Git, GitHub, Trello', 'Full Stack (Mobile & Desktop)', 'https://github.com/Mushhhhroom', NULL, 1),
('Cinema Ticketing System (MVP Award Winner)', 'Senior High School Capstone Project at Young Achievers\' School of Caloocan (S.Y 2022 – 2023). Main Programmer. Desktop application created using VB.NET and Microsoft Access to streamline box office ticket purchasing. Features a visual seat selection interface, automated billing, and an intuitive dashboard for controlling showtimes and monitoring daily sales—eliminating double booking and long queues. Awarded the prestigious MVP Award (S.Y. 2022 — 2023).', 'VB.NET (Windows Forms), Microsoft Access, OleDb Data Provider, SQL', 'Desktop Application', 'https://github.com/Mushhhhroom', NULL, 1),
('Personal Portfolio & Content Management System', 'Production-grade personal portfolio engineered with a dynamic resume system, live filterable project catalog, defensive security architecture (CSRF tokens, PDO prepared statements, XSS escaping, bcrypt hashing), and serverless cloud deployment.', 'PHP 8, PostgreSQL (Supabase), MySQL, JavaScript (ES6+), HTML5, CSS3', 'Web Application', 'https://github.com/Mushhhhroom/WebsitePort', 'https://portfolio-jairus-projects-6403693f.vercel.app', 1);
