const formElement = document.getElementById('form');
const inputElement = document.getElementById('input');
const resultElement = document.getElementById('result');
const scoreElement = document.getElementById('score');
const failedElement = document.getElementById('failed');
const passedElement = document.getElementById('passed');
const errorElement = document.getElementById('error');
let savedValue = '';

function populateList(list, items) {
  for (let i = 0; i < items.length; i++) {
    const listItem = document.createElement('li');
    listItem.append(document.createTextNode(items[i]));
    list.appendChild(listItem);
  }
}

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
      populateList(failedElement, result.failed);
    } else {
      const listItem = document.createElement('li');
      listItem.classList.add('info');
      listItem.append(document.createTextNode('No tests failed'));
      failedElement.appendChild(listItem);
    }
    if (result.passed.length > 0) {
      populateList(passedElement, result.passed);
    } else {
      const listItem = document.createElement('li');
      listItem.classList.add('info');
      listItem.append(document.createTextNode('No tests passed'));
      passedElement.appendChild(listItem);
    }
  })
  .catch(error => {
    if (savedValue.length > 0) inputElement.value = savedValue;
    resultElement.style.display = 'none';
    errorElement.style.display = 'block';
    console.error(error);
  });
}, false);
