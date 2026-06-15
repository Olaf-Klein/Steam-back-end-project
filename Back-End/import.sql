CREATE DATABASE IF NOT EXISTS backend_eindproject;
USE backend_eindproject;

DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS games;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(120) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    IsAdmin BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE TABLE IF NOT EXISTS games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(120) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(6, 2) NOT NULL DEFAULT 0.00,
    image VARCHAR(500) NOT NULL,
    genre VARCHAR(80) NOT NULL
);

CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    game_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    game_id INT NOT NULL,
    price_at_purchase DECIMAL(6, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
);

INSERT INTO users (username, email, password, IsAdmin) VALUES
('admin', 'admin@example.com', '$2y$10$examplehashedpasswordforadmin', TRUE),
('player', 'player@example.com', '$2y$10$examplehashedpasswordforplayer', FALSE);

INSERT INTO games (title, description, price, image, genre) VALUES
('Counter-Strike 2', 'Competitive tactical shooter with team-based matches.', 0.00, 'https://cdn.cloudflare.steamstatic.com/steam/apps/730/library_600x900_2x.jpg', 'Shooter'),
('Hades II', 'Action roguelike where you battle through mythological worlds.', 28.99, 'https://cdn.cloudflare.steamstatic.com/steam/apps/1145350/library_600x900_2x.jpg', 'Roguelike'),
('Stardew Valley', 'Farming, crafting, mining, and village life in a cozy pixel world.', 13.99, 'https://cdn.cloudflare.steamstatic.com/steam/apps/413150/library_600x900_2x.jpg', 'Simulation'),
('Baldur''s Gate 3', 'Party-based fantasy RPG with tactical combat and deep choices.', 59.99, 'https://cdn.cloudflare.steamstatic.com/steam/apps/1086940/library_600x900_2x.jpg', 'RPG'),
('Elden Ring', 'Open-world action RPG with challenging combat and exploration.', 59.99, 'https://cdn.cloudflare.steamstatic.com/steam/apps/1245620/library_600x900_2x.jpg', 'Action RPG'),
('Cyberpunk 2077', 'Story-driven open-world RPG set in Night City.', 59.99, 'https://cdn.cloudflare.steamstatic.com/steam/apps/1091500/library_600x900_2x.jpg', 'RPG');

INSERT INTO cart (user_id, game_id) VALUES
(2, 1),
(2, 3);

INSERT INTO orders (user_id) VALUES
(2);

INSERT INTO order_items (order_id, game_id, price_at_purchase) VALUES
(1, 3, 13.99);
