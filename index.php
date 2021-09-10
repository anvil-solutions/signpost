<?php require_once('./src/layout/header.php'); ?>
<main>
  <div class="card">
    <h2>Welcome to Signpost</h2>
    <p>
      Signpost is a web app for checking basic website performance. Enter a web page url below to start testing.
    </p>
    <form id="form" method="POST" action="./check">
      <input id="input" aria-label="Web Page URL" name="url" type="text" required>
      <button type="submit" class="btn">Check Now</button>
    </form>
  </div>
  <div id="result" class="card" style="display:none">
    <h2>Results</h2>
    <p>
      Below you can view the results of the performed tests.
    </p>
    <p>The site has achieved a score of <span id="score"></span>/100.</p>
    <details open>
      <summary><h3>Failed Tests <small class="indicator"></small></h3></summary>
      <ul id="failed"></ul>
    </details>
    <details>
      <summary><h3>Passed Tests <small class="indicator"></small></h3></summary>
      <ul id="passed"></ul>
    </details>
  </div>
  <div id="error" class="card" style="display:none">
    <h2>Test Failed</h2>
    <p>
      An unexpected error occured.
    </p>
  </div>
  <div class="card">
    <h2>About</h2>
    <p>
      Signpost was created by Anvil Solutions
    </p>
    <nav>
      <ul>
        <li><a href="https://fonts.google.com/icons">Material Icons by Google</a></li>
        <li><a href="https://github.com/anvil-solutions/signpost">GitHub</a></li>
        <li><a href="http://anvil-solutions.com/en/privacy">Privacy</a></li>
        <li><a href="http://anvil-solutions.com/en/imprint">Imprint</a></li>
    </nav>
  </div>
</main>
<script src="./js/main.js" async></script>
