<?php require_once('./src/layout/header.php'); ?>
<main>
  <h2>Welcome to Signpost</h2>
  <p>
    Signpost is a web app for checking basic website performance. Enter an web page url below to start testing.
  </p>
  <form id="form" method="POST" action="./check">
    <input id="input" aria-label="Web Page URL" name="url" type="text" required>
    <button type="submit" class="btn">Check Now</button>
  </form>
  <div id="result" style="display:none">
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
  <div id="error" style="display:none">
    <h2>Test Failed</h2>
    <p>
      An unexpected error occured.
    </p>
  </div>
</main>
<script>
  const formElement = document.getElementById('form');
  const inputElement = document.getElementById('input');
  const resultElement = document.getElementById('result');
  const scoreElement = document.getElementById('score');
  const failedElement = document.getElementById('failed');
  const passedElement = document.getElementById('passed');
  const errorElement = document.getElementById('error');
  let savedValue = '';
  formElement.addEventListener('submit', e => {
    e.preventDefault();
    const formData = new FormData(formElement);
    fetch('./check', {
      method: 'POST',
      body: formData,
    })
    .then(response => response.json())
    .then(result => {
      savedValue = inputElement.value;
      inputElement.value = result.url;
      errorElement.style.display = 'none';
      resultElement.style.display = 'block';
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
        listItem.classList.add('info');
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
        listItem.classList.add('info');
        listItem.append(document.createTextNode('No tests passed'));
        passedElement.appendChild(listItem);
      }
    })
    .catch(error => {
      if (savedValue.length < 0) inputElement.value = savedValue;
      resultElement.style.display = 'none';
      errorElement.style.display = 'block';
      console.error(error);
    });
  }, false);
</script>
