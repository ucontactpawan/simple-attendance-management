<<<<<<< HEAD
-- Create and use the database
DROP DATABASE IF EXISTS attendance_portal;
CREATE DATABASE attendance_portal;
USE attendance_portal;

-- Create employees table
=======
CREATE DATABASE attendance_portal;
USE attendance_portal;


>>>>>>> 84b16f637eeeb84293c21d5fc67f822c09b4048f
CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    contact VARCHAR(20),
    address VARCHAR(255),
    position ENUM('admin', 'employee', 'manager') DEFAULT 'employee',
<<<<<<< HEAD
    status ENUM('0', '1') DEFAULT '1',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create attendance table
=======
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

>>>>>>> 84b16f637eeeb84293c21d5fc67f822c09b4048f
CREATE TABLE attendance (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    employee_id INT(11) NOT NULL,
    date DATE NOT NULL,
    in_time VARCHAR(50),
    out_time VARCHAR(50),
    in_image VARCHAR(250),
    out_image VARCHAR(250),
<<<<<<< HEAD
    comments TEXT,
=======
>>>>>>> 84b16f637eeeb84293c21d5fc67f822c09b4048f
    status ENUM('0', '1') DEFAULT '0',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id)
<<<<<<< HEAD
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Create attendance_history table
=======
);

>>>>>>> 84b16f637eeeb84293c21d5fc67f822c09b4048f
CREATE TABLE attendance_history (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    creator_id INT(11) DEFAULT '0',
    attendance_id INT(11),
    employee_id INT(11) NOT NULL,
    action ENUM('IN', 'OUT') DEFAULT 'IN',
    date_time VARCHAR(50),
    image VARCHAR(255),
    comments TEXT,
    status ENUM('1', '0') DEFAULT '1',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (attendance_id) REFERENCES attendance(id)
<<<<<<< HEAD
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
=======
);
>>>>>>> 84b16f637eeeb84293c21d5fc67f822c09b4048f




