# FilerDB

A simplistic PHP flat file database designed to get your application up and running fast. Please note this package is currently in development and is not yet at a release.

# Usage

Please make sure your database directory has correct permissions for READ and WRITE.

### Instantiating

```
use FilerDB\Instance;

// Instantiate Database
$filerdb = new Instance([ 'path' => __DIR__ . '/database/' ]);
```

### Configuration

```
[

  /**
   * This is the main path for FilerDB.
   */
  'path' => __DIR__ . '/database',

  /**
   * If the database path does not exist, try
   * and create it.
   */
  'createDatabaseIfNotExist' => false,

  /**
   * If the insert and update logic handles
   * the createdAt and updatedAt timestamps
   * automatically
   */
  'includeTimestamps' => false

]
```

### Creating a database

```
$filerdb->databases->create('dev');
```

### Check if database exists

```
$filerdb->databases->exists('dev'); // Returns true or false
```

### List all databases

```
$filerdb->databases->list(); // Returns array
```

### Deleting a database

```
$filerdb->databases->delete('dev');
```

### Selecting a default database

Selecting a default database allows you to not have to specify the database on every call. Please refer to the below code on how to do that.

```
// Specify it in the configuration:
$filerdb = new Instance([
  'path' => __DIR__ . '/database/',
  'database' => 'database_name'
]);
```

or

```
$filerdb->selectDatabase('database_name');
```

With the above, you can now do the following:

```
$filerdb->collection('users')->all(); // Notice no ->database()
```

### List collections in a database

```
$filerdb->database('dev')->collections(); // Returns array
```

### Check if collection exists

```
$filerdb->database('dev')->collectionExists('dev'); // Returns true of false
```

### Creating a collection

```
$filerdb->database('dev')->createCollection('users');
```

### Deleting a collection

```
$filerdb->database('dev')->deleteCollection('users');
```

### Empty a collection

```
$filerdb->database('dev')->collection('users')->empty();
```

### Inserting a document

```
$filerdb->database('dev')->collection('users')->insert([
  'username' => 'test',
  'email'    => 'test@test.com'
]);
```

### Updating a document

```

// By a specific document
$filerdb
  ->database('dev')
  ->collection('users')
  ->id('ad23tasdg')
  ->update([
    'username' => 'test2'
  ]);

// Where all usernames equal test
$filerdb
  ->database('dev')
  ->collection('users')
  ->filter(['username' => 'test'])
  ->update([
    'username' => 'test2'
  ]);
```

### Deleting a document

```
// Specific document
$filerdb
  ->database('dev')
  ->collection('users')
  ->id('asdfwegd')
  ->delete();

// With filters
$filerdb
  ->database('dev')
  ->collection('users')
  ->filter(['username' => 'test'])
  ->delete();
```

### Retrieving all documents

```
$filerdb->database('dev')->collection('users')->all()
```

### Retrieving document by id

```
$filerdb
  ->database('dev')
  ->collection('users')
  ->id('asdf23g')
  ->get();
```

### Retrieving document by filters

```
// Get users with username of test and greater than age of 10.
$filerdb
  ->database('dev')
  ->collection('users')
  ->filter(['username' => 'test'])
  ->filter([ ['age', '>', '10'] ])
  ->get();
```

### Ordering documents by a field

```
// Get users with username of test and greater than age of 10.
$filerdb
  ->database('dev')
  ->collection('users')
  ->orderBy('username', 'asc')
  ->get();
```

### Limiting number of documents

```
// Pull upto 10 documents
$filerdb
  ->database('dev')
  ->collection('users')
  ->limit(10)
  ->get();
```

### Offsetting documents

```
// Pull upto 10 documents, but start at the
// 9th array key.
$filerdb
  ->database('dev')
  ->collection('users')
  ->limit(10, 9)
  ->get();
```

# Backups

You can now programmatically backup your database. You can do so by using the following code:

```
$filerdb->backup->create('file_name_here.zip');
```

This was provided so you can manually backup your database via your own command line script, or automatically via a cron job, or something similar.
