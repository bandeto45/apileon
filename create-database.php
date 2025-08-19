<?php
/**
 * Database initialization script for portable Apileon
 */

$dbFile = __DIR__ . '/database/apileon.sqlite';
$dbDir = dirname($dbFile);

if (!is_dir($dbDir)) {
    mkdir($dbDir, 0755, true);
}

try {
    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Creating database tables...\n";
    
    // Create users table
    $createUsersTable = <<<SQL
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
SQL;
    
    $pdo->exec($createUsersTable);
    
    // Create posts table
    $createPostsTable = <<<SQL
CREATE TABLE IF NOT EXISTS posts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    user_id INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
SQL;
    
    $pdo->exec($createPostsTable);
    
    // Create migrations table
    $createMigrationsTable = <<<SQL
CREATE TABLE IF NOT EXISTS migrations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    migration VARCHAR(255) NOT NULL,
    batch INTEGER NOT NULL,
    executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
SQL;
    
    $pdo->exec($createMigrationsTable);
    
    echo "Inserting sample data...\n";
    
    // Insert sample users
    $insertUsers = <<<SQL
INSERT OR IGNORE INTO users (id, name, email, password) VALUES 
(1, 'John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(2, 'Jane Smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
(3, 'Bob Johnson', 'bob@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
SQL;
    
    $pdo->exec($insertUsers);
    
    // Insert sample posts
    $insertPosts = <<<SQL
INSERT OR IGNORE INTO posts (id, title, content, user_id) VALUES 
(1, 'Welcome to Apileon', 'This is your first post in the portable Apileon framework!', 1),
(2, 'API Development Made Easy', 'Apileon provides a simple yet powerful way to build REST APIs.', 1),
(3, 'Portable Deployment', 'Now you can run Apileon anywhere without complex setup!', 2),
(4, 'SQLite Integration', 'Using SQLite makes deployment incredibly simple and portable.', 2),
(5, 'Sample Data', 'This post demonstrates the sample data included with portable Apileon.', 3);
SQL;
    
    $pdo->exec($insertPosts);
    
    // Insert migration records
    $insertMigrations = <<<SQL
INSERT OR IGNORE INTO migrations (migration, batch) VALUES 
('2023_01_01_000000_create_users_table', 1),
('2023_01_01_000001_create_posts_table', 1);
SQL;
    
    $pdo->exec($insertMigrations);
    
    echo "Database initialized successfully!\n";
    echo "Sample users created:\n";
    echo "- john@example.com (password: password)\n";
    echo "- jane@example.com (password: password)\n";
    echo "- bob@example.com (password: password)\n";
    
} catch (PDOException $e) {
    echo "Error creating database: " . $e->getMessage() . "\n";
    exit(1);
}
