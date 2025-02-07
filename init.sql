CREATE DATABASE IF NOT EXISTS hackin;
USE hackin;

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product VARCHAR(100) NOT NULL,
    description TEXT
);

CREATE TABLE IF NOT EXISTS secrets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    secret VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS urlencode (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- Add some initial data
INSERT INTO products (product, description) VALUES
('WiFi Pineapple', 'The ultimate rogue access point for wireless network auditing'),
('Rubber Ducky', 'USB device that types faster than any human - perfect for testing USB security'),
('Network Sniffer Pro', 'Capture and analyze network traffic with ease'),
('Password Cracker 9000', 'For educational purposes only - test the strength of your passwords'),
('Binary Analysis Toolkit', 'Reverse engineering made simple'),
('Secure VPN Bundle', 'Stay anonymous while testing network security'),
('Bug Bounty Starter Kit', 'Everything you need to start your ethical hacking career'),
('CTF Training Platform', 'Practice your capture the flag skills'),
('Encryption Toolkit Plus', 'Military-grade encryption tools for secure communications'),
('Social Engineering Manual', 'Learn about the human aspect of cybersecurity');

INSERT INTO secrets (secret) VALUES
('Did you know that some of the most famous historical figures, like Albert Einstein, were known to doodle while thinking? It’s a fun reminder that sometimes, creativity flourishes in the most unexpected ways.');


INSERT INTO urlencode (code) VALUES
('VSBhUmUgZzAwRCEgUmVtZW1iZXIsIGRvbid0IGNoZWF0ISBJdCB3aWxsIDBubHkgaHVydCB5b3VyIGxlYXJuaW5nIGluIHRoZSBlbmQh');


INSERT INTO users (username, password) VALUES
('admin', 'ur$glU92!jjI23');

