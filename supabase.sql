-- ==========================================================
-- Supabase (PostgreSQL) Schema & Seed Data
-- Jairus John Valdez Portfolio
-- Run this in your Supabase SQL Editor:
-- https://supabase.com/dashboard/project/<YOUR-PROJECT>/sql
-- ==========================================================

-- 1. Users Table (Admin authentication)
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    full_name VARCHAR(100) NOT NULL DEFAULT 'Jairus John Valdez',
    headline VARCHAR(150) NOT NULL DEFAULT 'Computer Science Student & Systems Developer',
    bio TEXT NULL,
    github VARCHAR(255) DEFAULT 'https://github.com',
    linkedin VARCHAR(255) DEFAULT 'https://linkedin.com',
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- 2. Resume Table (Dynamic resume sections and contents)
CREATE TABLE IF NOT EXISTS resume (
    id SERIAL PRIMARY KEY,
    section VARCHAR(50) NOT NULL,
    title VARCHAR(150) NOT NULL,
    subtitle VARCHAR(150) DEFAULT NULL,
    date_range VARCHAR(100) DEFAULT NULL,
    content TEXT NOT NULL,
    display_order INT DEFAULT 0,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- 3. Projects Table (Project showcase)
CREATE TABLE IF NOT EXISTS projects (
    id SERIAL PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    tech_stack VARCHAR(255) NOT NULL,
    category VARCHAR(50) NOT NULL DEFAULT 'Web Application',
    github_link VARCHAR(255) DEFAULT NULL,
    demo_link VARCHAR(255) DEFAULT NULL,
    featured SMALLINT NOT NULL DEFAULT 0,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- 4. Messages Table (Secure contact form inquiries)
CREATE TABLE IF NOT EXISTS messages (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(150) NOT NULL DEFAULT 'Portfolio Contact Request',
    message TEXT NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    is_read SMALLINT NOT NULL DEFAULT 0,
    created_at TIMESTAMPTZ DEFAULT CURRENT_TIMESTAMP
);

-- ==========================================================
-- SEED INITIAL DATA
-- Default Admin credentials:
-- Username: admin
-- Password: password123
-- ==========================================================

INSERT INTO users (username, password, email, full_name, headline, bio, github, linkedin)
VALUES (
    'admin',
    '$2y$12$7hT8WWdLxGoM4AQ63H565.wkYtsyIfFbrW3bE8QjDFSmyhNlw8pT.',
    'jairusjohnvaldez@gmail.com',
    'Jairus John Valdez',
    'Computer Science Student & Systems Developer',
    'Passionate Computer Science student specializing in secure system development, web technologies, database engineering, and software architecture. Committed to building robust, high-performance, and safe applications.',
    'https://github.com/jairusvaldez',
    'https://linkedin.com/in/jairusvaldez'
)
ON CONFLICT (username) DO UPDATE 
SET password = EXCLUDED.password, email = EXCLUDED.email;

-- Insert Resume Data
INSERT INTO resume (section, title, subtitle, date_range, content, display_order) VALUES
('education', 'Bachelor of Science in Computer Science', 'College of Computer Studies', '2022 - Present', 'Focusing on Data Structures & Algorithms, Database Management Systems (IM101), Object-Oriented Programming, and Secure Software Development.', 1),
('education', 'Senior High School - STEM Strand', 'Young Achievers School of Caloocan', '2020 - 2022', 'Graduated with academic honors with focus on science, mathematics, and introductory computer programming.', 2),
('experience', 'Full-Stack Developer & Lead Student Engineer', 'Academic Systems Development (NARP Portal)', '2024 - Present', 'Architected full-stack portal features integrating Next.js, Prisma ORM, and PostgreSQL. Built role-based access control and responsive interfaces for academic workflows.', 1),
('experience', 'Database Systems Engineer (Course Project)', 'IM101 - Database Systems Management', '2023 - 2024', 'Designed normalized relational schemas in MySQL and PostgreSQL, optimized queries with B-tree indexes, and implemented ACID-compliant transactional flows.', 2),
('skills', 'Programming Languages', 'Core Development', 'Proficient', 'PHP, JavaScript (ES6+), Python, C/C++, SQL, HTML5, CSS3', 1),
('skills', 'Frameworks & Tools', 'Libraries & Systems', 'Active', 'Node.js, Express.js, Tailwind CSS, Next.js, Prisma, Git, Docker (Basics)', 2),
('skills', 'Databases & Security', 'Data Management', 'Advanced', 'MySQL, PostgreSQL, Prepared Statements (PDO), CSRF Protection, Password Hashing (bcrypt), XSS Sanitization', 3),
('certifications', 'Information Management & Relational Database Design', 'University Certification', '2024', 'Verified competence in relational database design, SQL optimization, and stored procedures.', 1),
('certifications', 'Web Security & Secure Coding Fundamentals', 'Online Specialization', '2023', 'Covered OWASP Top 10 vulnerabilities, input validation, authentication architectures, and defense-in-depth principles.', 2);

-- Insert Projects Data
INSERT INTO projects (title, description, tech_stack, category, github_link, demo_link, featured) VALUES
('NARP Portal System', 'An enterprise-grade student and faculty portal providing automated record tracking, real-time notifications, and role-based permissions.', 'Next.js, TypeScript, Prisma, PostgreSQL, Tailwind CSS', 'Web Application', 'https://github.com/jairusvaldez/narp-portal', 'https://narp-portal.local', 1),
('Secure Portfolio & Content Management System', 'Dynamic personal portfolio equipped with an administrative dashboard, PDO prepared statements, CSRF protection, and dynamic resume management.', 'PHP 8, MySQL, JavaScript, CSS3', 'Full Stack', 'https://github.com/jairusvaldez/portfolio', 'http://localhost:8000', 1),
('IM101 Bookshop & Inventory Database', 'A normalized relational database system handling transactional book sales, customer orders, and automated stock alerts with ACID compliance.', 'PHP, MySQL, Bootstrap, SQL Optimization', 'Database System', 'https://github.com/jairusvaldez/bookshop-db', NULL, 1),
('Authentication & Session Guard Service', 'A dedicated authentication module demonstrating secure cookie storage, bcrypt password hashing, session regeneration, and anti-CSRF token verification.', 'PHP, MySQL, REST API', 'Security & Backend', 'https://github.com/jairusvaldez/auth-guard', NULL, 0),
('Cisco Network Topology & Packet Tracer Labs', 'Configured VLANs, OSPF routing protocols, DHCP snooping, and ACL security rules for campus network simulation.', 'Cisco Packet Tracer, Networking, Subnetting', 'Networking & Systems', 'https://github.com/jairusvaldez/networking-labs', NULL, 0);
