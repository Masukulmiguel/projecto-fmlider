<?php

/**
 * Database Seeder
 * 
 * Execute this to populate the database with initial data.
 * Run: php backend/database/seeders/DatabaseSeeder.php
 */

class DatabaseSeeder {
    private $db;

    public function __construct() {
        $this->db = new mysqli(
            'localhost',
            'root',
            '',
            'fmlider'
        );

        if ($this->db->connect_error) {
            die("Connection failed: " . $this->db->connect_error);
        }
    }

    public function seedUsers() {
        $check = $this->db->query("SELECT id FROM users WHERE email = 'admin@fmlider.co.ao' LIMIT 1");
        if ($check && $check->num_rows > 0) {
            echo "✓ Admin user já existe\n";
            return;
        }

        $adminPassword = password_hash('Admin@2026', PASSWORD_BCRYPT);

        $sql = "INSERT INTO users (username, name, email, phone, role, password, status, approval_status) VALUES
                ('admin', 'Admin User', 'admin@fmlider.co.ao', '+244 935141747', 'admin', '$adminPassword', 1, 'approved')";

        if ($this->db->query($sql) === TRUE) {
            echo "✓ Users seeded\n";
        } else {
            echo "✗ Error seeding users: " . $this->db->error . "\n";
        }
    }

    public function seedServices() {
        $services = [
            ['Desembaraço Aduaneiro', 'desembaraco-aduaneiro', 'Especialistas em importação, exportação e processos aduaneiros em Angola.', 1],
            ['Transportes', 'transportes', 'Transporte seguro de cargas em todo território nacional.', 1],
            ['Armazenagem', 'armazenagem', 'Espaços preparados para armazenagem de mercadorias e contentores.', 1],
            ['Door To Door', 'door-to-door', 'Soluções completas desde a origem até ao destino.', 1]
        ];

        foreach ($services as $service) {
            $sql = "INSERT INTO services (title, slug, description, status) VALUES 
                    ('{$service[0]}', '{$service[1]}', '{$service[2]}', {$service[3]})";
            
            if ($this->db->query($sql)) {
                echo "✓ Service '{$service[0]}' seeded\n";
            } else {
                echo "✗ Error: " . $this->db->error . "\n";
            }
        }
    }

    public function seedPartners() {
        $partners = [
            ['DHL', 'partner1.webp', 'https://www.dhl.com', 1],
            ['Maersk', 'partner2.png', 'https://www.maersk.com', 1],
            ['MSC', 'partner3.png', 'https://www.msc.com', 1],
            ['CMA CGM', 'partner4.webp', 'https://www.cma-cgm.com', 1]
        ];

        foreach ($partners as $partner) {
            $sql = "INSERT INTO partners (name, logo, website, status) VALUES 
                    ('{$partner[0]}', '{$partner[1]}', '{$partner[2]}', {$partner[3]})";
            
            if ($this->db->query($sql)) {
                echo "✓ Partner '{$partner[0]}' seeded\n";
            } else {
                echo "✗ Error: " . $this->db->error . "\n";
            }
        }
    }

    public function seedSettings() {
        $settings = [
            ['company_name', 'FMLider Transitário & Logística'],
            ['company_phone', '+244 935141747'],
            ['company_email', 'geral@fmlider.co.ao'],
            ['company_address', 'FMLider Base, Estrada da Pedreira, Bairro da Vidrul, Cacuaco'],
            ['company_description', 'Especialistas em logística, transportes e desembaraço aduaneiro em Angola'],
            ['founded_year', '2017'],
            ['employees_count', '60'],
            ['countries_partners', '32']
        ];

        foreach ($settings as $setting) {
            $value = $this->db->real_escape_string($setting[1]);
            $sql = "INSERT INTO settings (`key`, `value`) VALUES ('{$setting[0]}', '{$value}')";

            if ($this->db->query($sql)) {
                echo "✓ Setting '{$setting[0]}' seeded\n";
            } else {
                echo "✗ Error: " . $this->db->error . "\n";
            }
        }
    }

    public function run() {
        echo "\n=== Database Seeding Started ===\n\n";
        $this->seedUsers();
        $this->seedServices();
        $this->seedPartners();
        $this->seedSettings();
        echo "\n=== Database Seeding Complete ===\n\n";
    }
}

// Run seeder
if (php_sapi_name() === 'cli') {
    $seeder = new DatabaseSeeder();
    $seeder->run();
}
