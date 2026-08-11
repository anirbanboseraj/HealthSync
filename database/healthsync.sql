-- ==========================================
-- HealthSync 2.0 Database
-- Author: Anirban Bose
-- ==========================================

-- ==========================================
-- Users Table
-- ==========================================

CREATE TABLE users (

    id INT AUTO_INCREMENT PRIMARY KEY,

    fullname VARCHAR(100) NOT NULL,

    email VARCHAR(100) UNIQUE NOT NULL,

    password VARCHAR(255) NOT NULL,

    role ENUM('patient','doctor','admin') NOT NULL,

    status ENUM('active','inactive') DEFAULT 'active',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);

-- ==========================================
-- Departments Table
-- ==========================================

CREATE TABLE departments (

    id INT AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(100) NOT NULL UNIQUE,

    description TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

);

INSERT INTO departments (name, description) VALUES
('Cardiology', 'Heart and blood vessel treatment'),
('Neurology', 'Brain and nervous system'),
('Orthopedics', 'Bones and joints'),
('Pediatrics', 'Child healthcare'),
('Dermatology', 'Skin treatment'),
('General Medicine', 'General health consultation'),
('ENT', 'Ear, Nose and Throat'),
('Gynecology', 'Women''s health'),
('Psychiatry', 'Mental health'),
('Emergency', 'Emergency medical services');



-- ==========================================
-- Doctors Table
-- ==========================================

CREATE TABLE doctors (

    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL UNIQUE,

    department_id INT NOT NULL,

    specialization VARCHAR(150),

    qualification VARCHAR(150),

    experience INT DEFAULT 0,

    consultation_fee DECIMAL(10,2) DEFAULT 0.00,

    phone VARCHAR(20),

    profile_image VARCHAR(255) DEFAULT 'default-doctor.png',

    bio TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE,

    FOREIGN KEY (department_id)
        REFERENCES departments(id)
        ON DELETE RESTRICT

);



-- ==========================================
-- Patients Table
-- ==========================================

CREATE TABLE patients (

    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL UNIQUE,

    date_of_birth DATE,

    gender ENUM('Male','Female','Other'),

    blood_group ENUM(
        'A+','A-',
        'B+','B-',
        'AB+','AB-',
        'O+','O-'
    ),

    phone VARCHAR(20),

    address TEXT,

    emergency_contact VARCHAR(20),

    profile_image VARCHAR(255) DEFAULT 'default-patient.png',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE

);


-- ==========================================
-- Appointments Table
-- ==========================================

CREATE TABLE appointments (

    id INT AUTO_INCREMENT PRIMARY KEY,

    patient_id INT NOT NULL,

    doctor_id INT NOT NULL,

    appointment_date DATE NOT NULL,

    appointment_time TIME NOT NULL,

    reason TEXT,

    status ENUM(
        'Pending',
        'Approved',
        'Completed',
        'Cancelled'
    ) DEFAULT 'Pending',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (patient_id)
        REFERENCES patients(id)
        ON DELETE CASCADE,

    FOREIGN KEY (doctor_id)
        REFERENCES doctors(id)
        ON DELETE CASCADE

);



-- ==========================================
-- Medical Records Table
-- ==========================================

CREATE TABLE medical_records (

    id INT AUTO_INCREMENT PRIMARY KEY,

    appointment_id INT NOT NULL UNIQUE,

    patient_id INT NOT NULL,

    doctor_id INT NOT NULL,

    diagnosis TEXT,

    prescription TEXT,

    doctor_notes TEXT,

    report_file VARCHAR(255),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (appointment_id)
        REFERENCES appointments(id)
        ON DELETE CASCADE,

    FOREIGN KEY (patient_id)
        REFERENCES patients(id)
        ON DELETE CASCADE,

    FOREIGN KEY (doctor_id)
        REFERENCES doctors(id)
        ON DELETE CASCADE

);


-- ==========================================
-- Prescriptions Table
-- ==========================================

CREATE TABLE prescriptions (

    id INT AUTO_INCREMENT PRIMARY KEY,

    medical_record_id INT NOT NULL,

    medicine_name VARCHAR(150) NOT NULL,

    dosage VARCHAR(100),

    frequency VARCHAR(100),

    duration VARCHAR(100),

    instructions TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (medical_record_id)
        REFERENCES medical_records(id)
        ON DELETE CASCADE

);


-- ==========================================
-- Medical Reports Table
-- ==========================================

CREATE TABLE medical_reports (

    id INT AUTO_INCREMENT PRIMARY KEY,

    medical_record_id INT NOT NULL,

    report_title VARCHAR(150) NOT NULL,

    report_type ENUM(
        'Blood Test',
        'X-Ray',
        'MRI',
        'CT Scan',
        'ECG',
        'Ultrasound',
        'Prescription',
        'Other'
    ) DEFAULT 'Other',

    file_name VARCHAR(255) NOT NULL,

    uploaded_by ENUM('doctor','patient','admin') NOT NULL,

    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (medical_record_id)
        REFERENCES medical_records(id)
        ON DELETE CASCADE

);


-- ==========================================
-- Notifications Table
-- ==========================================

CREATE TABLE notifications (

    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,

    title VARCHAR(150) NOT NULL,

    message TEXT NOT NULL,

    is_read BOOLEAN DEFAULT FALSE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE

);


-- ==========================================
-- Activity Logs Table
-- ==========================================

CREATE TABLE activity_logs (

    id INT AUTO_INCREMENT PRIMARY KEY,

    user_id INT NOT NULL,

    activity VARCHAR(255) NOT NULL,

    ip_address VARCHAR(50),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE

);