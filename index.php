<?php require_once('./src/layout/header.php'); ?>
<main class="welcome">
  <h2>Welcome to Signpost</h2>
  <p>
    Signpost is a web app for checking basic website performance. Enter an web page url below to start testing.
  </p>
  <form method="POST">
    <label><div>URL: </div><input name="url" type="text" required></label>
    <div class="btnContainer"><button type="submit" class="btn">Check Now</button></div>
  </form>
</main>
