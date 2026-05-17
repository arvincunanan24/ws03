<?php

namespace App\Controllers;

use Framework\Database;

class HomeController
{
    protected $db;

    public function __construct()
    {
        $config = [
            'host'     => 'localhost',
            'port'     => '3306',
            'dbname'   => 'ws03',
            'username' => 'root',
            'password' => ''
        ];

        $this->db = new Database($config);
    }

    public function index()
    {
        $listings = $this->db->query('SELECT * FROM listings ORDER BY created_at DESC LIMIT 6')->fetchAll();

        loadView('home', ['listings' => $listings]);
    }
}
