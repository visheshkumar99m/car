-- Database creation (run this if the database doesn't exist)
CREATE DATABASE IF NOT EXISTS cars_data;

-- Use the database
USE cars_data;

-- Brands table
CREATE TABLE IF NOT EXISTS brands (
    brand_id INT AUTO_INCREMENT PRIMARY KEY,
    brand_name VARCHAR(100) NOT NULL,
    logo VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Cars table
CREATE TABLE IF NOT EXISTS cars (
    car_id INT AUTO_INCREMENT PRIMARY KEY,
    brand_id INT,
    car_name VARCHAR(100) NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    image VARCHAR(255),
    year INT,
    type VARCHAR(50),
    fuel VARCHAR(50),
    rating DECIMAL(3,1),
    seats INT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (brand_id) REFERENCES brands(brand_id) ON DELETE CASCADE
);

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(12,2) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order items table
CREATE TABLE IF NOT EXISTS order_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    car_id INT NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    quantity INT DEFAULT 1,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (car_id) REFERENCES cars(car_id) ON DELETE CASCADE
);

-- Sample brand data
INSERT INTO brands (brand_name, logo, description) VALUES
('Hyundai', 'https://www.carlogos.org/car-logos/hyundai-logo.png', 'Hyundai Motor Company is a South Korean multinational automotive manufacturer.'),
('Mahindra', 'https://www.carlogos.org/car-logos/mahindra-logo.png', 'Mahindra & Mahindra Limited is an Indian multinational automobile manufacturing corporation.'),
('Mercedes', 'https://www.carlogos.org/car-logos/mercedes-benz-logo.png', 'Mercedes-Benz is a German global automobile marque and a division of Daimler AG.'),
('Honda', 'https://www.carlogos.org/car-logos/honda-logo.png', 'Honda Motor Co., Ltd. is a Japanese public multinational conglomerate manufacturer of automobiles.');

-- Sample car data
INSERT INTO cars (brand_id, car_name, price, image, year, type, fuel, rating, seats, description) VALUES
(1, 'Hyundai i20', 800000, 'https://stimg.cardekho.com/images/carexteriorimages/630x420/Hyundai/i20/10108/1682674395410/front-left-side-47.jpg?tr=w-456', 2022, 'Hatchback', 'Petrol', 4.2, 5, 'The Hyundai i20 is a hatchback produced by the South Korean manufacturer Hyundai.'),
(1, 'Hyundai Creta', 1200000, 'https://stimg.cardekho.com/images/carexteriorimages/630x420/Hyundai/Creta/10544/1685527904354/front-left-side-47.jpg?tr=w-456', 2022, 'SUV', 'Petrol/Diesel', 4.5, 5, 'The Hyundai Creta is a compact SUV produced by the South Korean manufacturer Hyundai.'),
(2, 'Mahindra Thar', 1600000, 'https://stimg.cardekho.com/images/carexteriorimages/630x420/Mahindra/Thar/10585/1690351800432/front-left-side-47.jpg?tr=w-456', 2023, 'SUV', 'Petrol/Diesel', 4.6, 4, 'The Mahindra Thar is an off-road SUV manufactured by Mahindra & Mahindra.'),
(2, 'Mahindra XUV700', 1800000, 'https://stimg.cardekho.com/images/carexteriorimages/630x420/Mahindra/XUV700/10591/1690434712975/front-left-side-47.jpg?tr=w-456', 2023, 'SUV', 'Petrol/Diesel', 4.7, 7, 'The Mahindra XUV700 is a mid-size crossover SUV manufactured by Mahindra & Mahindra.'),
(3, 'Mercedes-Benz A-Class', 4200000, 'https://stimg.cardekho.com/images/carexteriorimages/630x420/Mercedes-Benz/A-Class-Limousine/8467/1584696878430/front-left-side-47.jpg?tr=w-456', 2021, 'Sedan', 'Petrol', 4.8, 5, 'The Mercedes-Benz A-Class is a subcompact executive car produced by the German automobile manufacturer Mercedes-Benz.'),
(3, 'Mercedes-Benz GLA', 5000000, 'https://stimg.cardekho.com/images/carexteriorimages/630x420/Mercedes-Benz/GLA/9702/1621425952883/front-left-side-47.jpg?tr=w-456', 2022, 'SUV', 'Petrol', 4.9, 5, 'The Mercedes-Benz GLA is a subcompact luxury crossover SUV manufactured by Mercedes-Benz.'),
(4, 'Honda City', 1100000, 'https://stimg.cardekho.com/images/carexteriorimages/630x420/Honda/City/9710/1677914238296/front-left-side-47.jpg?tr=w-456', 2021, 'Sedan', 'Petrol/Diesel', 4.4, 5, 'The Honda City is a subcompact car which has been produced by the Japanese manufacturer Honda since 1981.'),
(4, 'Honda Amaze', 700000, 'https://stimg.cardekho.com/images/carexteriorimages/630x420/Honda/Amaze/10496/1680249225456/front-left-side-47.jpg?tr=w-456', 2020, 'Sedan', 'Petrol/Diesel', 4.1, 5, 'The Honda Amaze is a 4-door sedan produced by Honda Cars India since 2013.');

-- Sample admin user (password: admin123)
INSERT INTO users (name, email, password, is_admin) VALUES
('Admin User', 'admin@carwale.com', '$2y$10$NJ.lvAJqNLNZRYkynJ8T2uGfUmEH9gYlICLPjSd5KDFLz3bIx4yua', 1);

-- Sample regular user (password: user123)
INSERT INTO users (name, email, password, is_admin) VALUES
('Regular User', 'user@example.com', '$2y$10$hcSrFOWUmHpNx9WvDv99Ru8MQa/Mbbk6F0E6dQBCEEJsBMoUiBp7K', 0); 