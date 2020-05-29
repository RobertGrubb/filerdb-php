# FilerDB

A simplistic PHP flat file database designed to get your application up and running fast. Please note this package is currently in development and is not yet at a release.

# Todo

- [ ] Ability to update a collection document.
- [ ] Ability to offset.

# Usage

Please make sure your database directory has correct permissions for READ and WRITE.

Be sure to also checkout the example folder, as there may be some more examples shown there until I have a chance to get a decent documentation up.

```
use FilerDB\Instance;

// Instantiate Database
$filerdb = new Instance([ 'DATABASE_PATH' => __DIR__ . '/database/' ]);

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
```
