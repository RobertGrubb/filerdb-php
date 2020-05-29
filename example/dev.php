<?php

require '../vendor/autoload.php';
require_once __DIR__ . '/../src/FilerDB.php';

// Instantiate Database
$filerdb = new FilerDB\Instance([ 'DATABASE_PATH' => __DIR__ . '/database/' ]);

// Query with filters, orders, and limits
$data = $filerdb
  ->database('test')
  ->collection('users')
  ->filter(['age' => 10])
  ->orderBy('username', 'desc')
  ->limit(1)
  ->get();

print_r($data);

// Inserts a new user
$filerdb
  ->database('test')
  ->collection('users')
  ->insert([
    'username' => 'Bob',
    'email' => 'bob@test.com'
  ]);

// Deleting a document with filters
$filerdb
  ->database('test')
  ->collection('users')
  ->delete();
