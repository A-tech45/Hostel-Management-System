-- =============================================
-- Hostel Management System - Database Setup
-- =============================================

DROP DATABASE IF EXISTS hostel_management;
CREATE DATABASE hostel_management;
USE hostel_management;

-- -----------------------------------------
-- 1. Admin Table
-- -----------------------------------------
CREATE TABLE admin (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
);

-- -----------------------------------------
-- 2. Hostel Table
-- -----------------------------------------
CREATE TABLE hostel (
  Hostel_id INT AUTO_INCREMENT PRIMARY KEY,
  Hostel_name VARCHAR(100) NOT NULL,
  location VARCHAR(200),
  Total_rooms INT DEFAULT 0
);

-- -----------------------------------------
-- 3. Room Table
-- -----------------------------------------
CREATE TABLE room (
  Room_id INT AUTO_INCREMENT PRIMARY KEY,
  Room_name VARCHAR(50) NOT NULL,
  Room_type ENUM('Single','Double','Triple') DEFAULT 'Double',
  capacity INT DEFAULT 2,
  status ENUM('Available','Occupied','Maintenance') DEFAULT 'Available',
  Hostel_id INT,
  FOREIGN KEY (Hostel_id) REFERENCES hostel(Hostel_id) ON DELETE CASCADE
);

-- -----------------------------------------
-- 4. Student Table (with login credentials)
-- -----------------------------------------
CREATE TABLE student (
  Student_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  gender ENUM('Male','Female','Other') NOT NULL,
  course VARCHAR(100),
  semester INT,
  phone VARCHAR(15),
  email VARCHAR(100),
  Room_id INT,
  FOREIGN KEY (Room_id) REFERENCES room(Room_id) ON DELETE SET NULL
);

-- -----------------------------------------
-- 6. Warden Table (with login credentials)
-- -----------------------------------------
CREATE TABLE warden (
  Warden_id INT AUTO_INCREMENT PRIMARY KEY,
  Name VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  Phone VARCHAR(15),
  Email VARCHAR(100),
  Hostel_id INT,
  FOREIGN KEY (Hostel_id) REFERENCES hostel(Hostel_id) ON DELETE SET NULL
);

-- -----------------------------------------
-- 7. Payment Table
-- -----------------------------------------
CREATE TABLE payment (
  Payment_id INT AUTO_INCREMENT PRIMARY KEY,
  Payment_Date DATE,
  amount DECIMAL(10,2),
  status ENUM('Paid','Pending','Overdue') DEFAULT 'Pending',
  Student_id INT,
  FOREIGN KEY (Student_id) REFERENCES student(Student_id) ON DELETE CASCADE
);

-- -----------------------------------------
-- 8. Attendance Table
-- -----------------------------------------
CREATE TABLE attendance (
  Attendance_id INT AUTO_INCREMENT PRIMARY KEY,
  Date DATE,
  In_time TIME,
  Out_time TIME,
  Student_id INT,
  FOREIGN KEY (Student_id) REFERENCES student(Student_id) ON DELETE CASCADE
);

-- -----------------------------------------
-- 9. Complaint Table
-- -----------------------------------------
CREATE TABLE complaint (
  Complaint_id INT AUTO_INCREMENT PRIMARY KEY,
  Student_id INT,
  Description TEXT,
  Date DATE,
  Status ENUM('Pending','In Progress','Resolved') DEFAULT 'Pending',
  FOREIGN KEY (Student_id) REFERENCES student(Student_id) ON DELETE CASCADE
);

-- -----------------------------------------
-- 10. Leave Request Table
-- -----------------------------------------
CREATE TABLE leave_request (
  Leave_id INT AUTO_INCREMENT PRIMARY KEY,
  Student_id INT NOT NULL,
  Start_date DATE NOT NULL,
  End_date DATE NOT NULL,
  Reason VARCHAR(500) NOT NULL,
  Status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',
  Reviewed_by_role ENUM('Admin','Warden') DEFAULT NULL,
  Reviewed_by_id INT DEFAULT NULL,
  Reviewed_at DATETIME DEFAULT NULL,
  Requested_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (Student_id) REFERENCES student(Student_id) ON DELETE CASCADE
);

-- -----------------------------------------
-- 11. Student Password Reset
-- -----------------------------------------
CREATE TABLE student_password_reset (
  Reset_id INT AUTO_INCREMENT PRIMARY KEY,
  Student_id INT NOT NULL,
  otp_hash VARCHAR(255) NOT NULL,
  expires_at DATETIME NOT NULL,
  used_at DATETIME DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (Student_id) REFERENCES student(Student_id) ON DELETE CASCADE,
  INDEX (Student_id),
  INDEX (expires_at)
);

-- =============================================
-- SEED DATA
-- =============================================

-- Admin (plaintext password for LAMPP simplicity)
INSERT INTO admin (username, password) VALUES ('admin', 'admin123');

-- Hostels
INSERT INTO hostel (Hostel_name, location, Total_rooms) VALUES
('Boys Hostel', 'Block A, Main Campus', 3),
('Girls Hostel', 'Block B, Main Campus', 3);

-- Rooms (3 in Boys Hostel, 3 in Girls Hostel)
INSERT INTO room (Room_name, Room_type, capacity, status, Hostel_id) VALUES
('B-101', 'Single',  1, 'Occupied',   1),
('B-102', 'Double',  2, 'Occupied',   1),
('B-103', 'Triple',  3, 'Available',  1),
('G-101', 'Single',  1, 'Occupied',   2),
('G-102', 'Double',  2, 'Available',  2),
('G-103', 'Triple',  3, 'Available',  2);

-- Wardens (with login credentials: username/password)
INSERT INTO warden (Name, username, password, Phone, Email, Hostel_id) VALUES
('Dr. Sharma',  'sharma', 'warden123', '9900000001', 'sharma@hostel.com', 1),
('Mrs. Gupta',  'gupta',  'warden123', '9900000002', 'gupta@hostel.com',  2);

-- Students (with login credentials: username/password)
INSERT INTO student (name, username, password, gender, course, semester, phone, email, Room_id) VALUES
('Rahul Verma', 'rahul', 'student123', 'Male',   'B.Tech CS', 4, '9876500001', 'rahul@student.com', 1),
('Priya Singh', 'priya', 'student123', 'Female', 'B.Tech IT', 3, '9876500002', 'priya@student.com', 4),
('Amit Kumar',  'amit',  'student123', 'Male',   'BCA',       2, '9876500003', 'amit@student.com',  2);

-- Payments
INSERT INTO payment (Payment_Date, amount, status, Student_id) VALUES
('2025-04-01', 25000.00, 'Paid',    1),
('2025-04-15', 22000.00, 'Pending', 2),
('2025-03-20', 20000.00, 'Paid',    3);

-- Attendance
INSERT INTO attendance (Date, In_time, Out_time, Student_id) VALUES
('2025-05-20', '08:00:00', '18:00:00', 1),
('2025-05-20', '09:00:00', '17:30:00', 2);
