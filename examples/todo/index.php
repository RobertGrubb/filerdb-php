<?php

// Include a file that returns the filerdb instance.
$filerdb = require __DIR__ . '/filerdb.php';

if (!$filerdb) die('FilerDB was unable to instantiate.');

// Current action
$action = (isset($_GET['a']) ? $_GET['a'] : false);

if ($action === 'submit') {
  $todo = (isset($_POST['todo']) ? $_POST['todo'] : false);

  if ($todo !== false) {
    $filerdb->collection('items')->insert([
      'title' => $todo
    ]);
  }

  header('Location: index.php');
  exit;

} elseif ($action === 'remove') {
  $id = (isset($_GET['id']) ? $_GET['id'] : false);

  if ($id !== false) {
    $filerdb->collection('items')->id($id)->delete();
  }

  header('Location: index.php');
  exit;
}

// Retrieve all todo items
$items = $filerdb->collection('items')->all();

?>

<h1>Todo Items</h1>

<form action="index.php?a=submit" method="POST">
  <input type="text" name="todo" />
  <button type="submit">Add</button>
</form>

<ul>
  <?php foreach ($items as $item): ?>
    <li><?= $item->title; ?>&nbsp;&nbsp;<a href="index.php?a=remove&id=<?= $item->id; ?>">[X]</a></li>
  <?php endforeach; ?>
</ul>
