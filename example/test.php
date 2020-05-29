<?php

require '../vendor/autoload.php';
require_once __DIR__ . '/../src/FilerDB.php';

use FilerDB\Instance;

// Instantiate Database
$filerdb = new Instance([
  'path' => __DIR__ . '/database/'
]);

// Create a new database
$filerdb->databases->create('dev');

// Print the list of databases
print_r($filerdb->databases->list());

// Create a collection called 'users'
$filerdb->database('dev')->createCollection('users');

// List the collections
print_r($filerdb->database('dev')->collections());

// Insert a new document
$filerdb->database('dev')->collection('users')->insert([
  'username' => 'test',
  'email'    => 'test@test.com'
]);

// Get all documents from collection
print_r($filerdb->database('dev')->collection('users')->all());

// Delete the database
$filerdb->databases->delete('dev');
