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
    <h2>Result</h2>
    <p>
      Below you can see the results of the performed tests.
    </p>
    <h3>Failed Tests</h3>
    <ul id="failed"></ul>
    <h3>Passed Tests</h3>
    <ul id="passed"></ul>
  </div>
</main>
<script>
  const formElement = document.getElementById('form');
  const resultElement = document.getElementById('result');
  const failedElement = document.getElementById('failed');
  const passedElement = document.getElementById('passed');
  formElement.addEventListener('submit', e => {
    e.preventDefault();
    const formData = new FormData(formElement);
    fetch('./check', {
      method: 'POST',
      body: formData,
    })
    .then(response => response.json())
    .then(result => {
      resultElement.style.display = 'block';
      failedElement.innerHTML = '';
      passedElement.innerHTML = '';
      for (let i = 0; i < result.failed.length; i++) {
        const listItem = document.createElement('li');
        listItem.append(document.createTextNode(result.failed[i]));
        failedElement.appendChild(listItem);
      }
      for (let i = 0; i < result.passed.length; i++) {
        const listItem = document.createElement('li');
        listItem.append(document.createTextNode(result.passed[i]));
        passedElement.appendChild(listItem);
      }
    })
    .catch(error => {
      resultElement.style.display = 'none';
    });
  }, false);
</script>
