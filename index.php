<?php require_once('./src/layout/header.php'); ?>
<main class="welcome">
  <h2>Welcome to Signpost</h2>
  <p>
    Signpost is a web app for checking basic website performance. Enter an web page url below to start testing.
  </p>
  <form id="form" method="POST" action="./check">
    <label><div>URL: </div><input name="url" type="text" required></label>
    <div class="btnContainer"><button type="submit" class="btn">Check Now</button></div>
  </form>
  <div id="result" style="display:none">
    <h2>Results for "<span id="url"><span>"</h2>
    <p>
      Below you can see the results of the performed tests.
    </p>
    <h3>Score</h3>
    <p>The site has achieved a score of <span id="score"></span>/100</p>
    <h3>Failed Tests</h3>
    <ul id="failed"></ul>
    <h3>Passed Tests</h3>
    <ul id="passed"></ul>
  </div>
  <div id="error" style="display:none">
    <h2>Test Failed</h2>
    <p>
      An unexpected error occured.
    </p>
  </div>
</main>
<script>
  const formElement = document.getElementById('form');
  const resultElement = document.getElementById('result');
  const urlElement = document.getElementById('url');
  const scoreElement = document.getElementById('score');
  const failedElement = document.getElementById('failed');
  const passedElement = document.getElementById('passed');
  const errorElement = document.getElementById('error');
  formElement.addEventListener('submit', e => {
    e.preventDefault();
    const formData = new FormData(formElement);
    fetch('./check', {
      method: 'POST',
      body: formData,
    })
    .then(response => response.json())
    .then(result => {
      errorElement.style.display = 'none';
      resultElement.style.display = 'block';
      urlElement.innerHTML = result.url;
      scoreElement.innerHTML = parseInt(result.passed.length / (result.passed.length + result.failed.length) * 100, 10);
      failedElement.innerHTML = '';
      passedElement.innerHTML = '';
      if (result.failed.length > 0) {
        for (let i = 0; i < result.failed.length; i++) {
          const listItem = document.createElement('li');
          listItem.append(document.createTextNode(result.failed[i]));
          failedElement.appendChild(listItem);
        }
      } else {
        const listItem = document.createElement('li');
        listItem.append(document.createTextNode('No tests failed'));
        failedElement.appendChild(listItem);
      }
      if (result.passed.length > 0) {
        for (let i = 0; i < result.passed.length; i++) {
          const listItem = document.createElement('li');
          listItem.append(document.createTextNode(result.passed[i]));
          passedElement.appendChild(listItem);
        }
      } else {
        const listItem = document.createElement('li');
        listItem.append(document.createTextNode('No tests passed'));
        passedElement.appendChild(listItem);
      }
    })
    .catch(error => {
      resultElement.style.display = 'none';
      errorElement.style.display = 'block';
      console.error(error);
    });
  }, false);
</script>
