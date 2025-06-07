
document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("searchHabits");
  const loadMoreBtn = document.getElementById("loadMoreHabits");
  let offset = 0;
  const limit = 10;

  function fetchHabits(query = "", append = false) {
    fetch(`scripts/search_habits.php?query=${query}&offset=${offset}&limit=${limit}`)
      .then(response => response.text())
      .then(data => {
        const container = document.getElementById("habitsList");
        if (append) {
          container.innerHTML += data;
        } else {
          container.innerHTML = data;
        }
      });
  }

  if (searchInput) {
    searchInput.addEventListener("input", function () {
      offset = 0;
      fetchHabits(this.value);
    });
  }

  if (loadMoreBtn) {
    loadMoreBtn.addEventListener("click", function () {
      offset += limit;
      fetchHabits(searchInput.value, true);
    });
  }

  fetchHabits(); // Initial load
});
