const searchInput = document.getElementById("admin-search");
const sortSelect = document.getElementById("admin-sort");
const tableBody = document.getElementById("admin-users-body");
const visibleCount = document.getElementById("admin-visible-count");
const emptyState = document.getElementById("admin-empty-state");
const profileTrigger = document.getElementById("admin-profile-trigger");
const profilePanel = document.getElementById("admin-profile-panel");
const profileOverlay = document.getElementById("admin-profile-overlay");
const profileClose = document.getElementById("admin-profile-close");

if (profileTrigger && profilePanel && profileOverlay && profileClose) {
  const openProfile = () => {
    profileTrigger.setAttribute("aria-expanded", "true");
    profilePanel.setAttribute("aria-hidden", "false");
    profilePanel.classList.add("is-open");
    profileOverlay.hidden = false;
    document.body.classList.add("profile-open");
  };

  const closeProfile = () => {
    profileTrigger.setAttribute("aria-expanded", "false");
    profilePanel.setAttribute("aria-hidden", "true");
    profilePanel.classList.remove("is-open");
    profileOverlay.hidden = true;
    document.body.classList.remove("profile-open");
  };

  profileTrigger.addEventListener("click", openProfile);
  profileClose.addEventListener("click", closeProfile);
  profileOverlay.addEventListener("click", closeProfile);
  window.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      closeProfile();
    }
  });
}

if (searchInput && sortSelect && tableBody && emptyState) {
  const rows = Array.from(tableBody.querySelectorAll("tr[data-user-row]"));

  const normalize = (value) => value.toLowerCase().trim();

  function applyFilters() {
    const query = normalize(searchInput.value);
    const sortValue = sortSelect.value;

    const filteredRows = rows.filter((row) => {
      const haystack = normalize(row.dataset.searchText || "");
      const matches = query === "" || haystack.includes(query);
      row.hidden = !matches;
      return matches;
    });

    filteredRows.sort((left, right) => {
      const leftName = normalize(left.dataset.userName || "");
      const rightName = normalize(right.dataset.userName || "");
      return sortValue === "DESC"
        ? rightName.localeCompare(leftName)
        : leftName.localeCompare(rightName);
    });

    filteredRows.forEach((row) => tableBody.appendChild(row));

    if (visibleCount) {
      visibleCount.textContent = String(filteredRows.length);
    }
    emptyState.hidden = filteredRows.length > 0;
  }

  searchInput.addEventListener("input", applyFilters);
  sortSelect.addEventListener("change", applyFilters);
  applyFilters();
}
