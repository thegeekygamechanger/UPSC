document.getElementById('todoForm').addEventListener('submit', function (e) {
  e.preventDefault();
  const title = document.getElementById('todoTitle').value.trim();
  if (title) {
    addTodo(title);
    document.getElementById('todoTitle').value = '';
  }
});

function addTodo(title) {
  const list = document.getElementById('todoList');
  const li = document.createElement('li');
  li.textContent = title;
  list.appendChild(li);
}

function logStudySession() {
  const hours = parseInt(document.getElementById('hoursToday').textContent);
  document.getElementById('hoursToday').textContent = hours + 1;
}

function showView(viewId) {
  document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
  document.getElementById(viewId).classList.add('active');

  document.querySelectorAll('.sidebar nav a').forEach(a => a.classList.remove('active'));
  document.querySelectorAll('.sidebar nav a').forEach(a => {
    if (a.textContent.toLowerCase().includes(viewId)) {
      a.classList.add('active');
    }
  });
}
