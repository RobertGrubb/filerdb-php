# FilerDB

A simplistic PHP flat file database designed to get your application up and running fast. Please note this package is currently in development and is not yet at a release.

# Todo

- [ ] Add forward slash at end of path if not provided
- [ ] Make filtering a little easier when there are conditionals.
- [ ] Ability to offset.

# Usage

Please make sure your database directory has correct permissions for READ and WRITE.

### Instantiating

```
use FilerDB\Instance;

// Instantiate Database
$filerdb = new Instance([ 'DATABASE_PATH' => __DIR__ . '/database/' ]);
```

### Creating a database

```
$filerdb->databases->create('dev');
```

### Deleting a database

```
$filerdb->databases->delete('dev');
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
