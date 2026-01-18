(function () {
  function ready(fn) {
    if (document.readyState !== "loading") {
      fn();
    } else document.addEventListener("DOMContentLoaded", fn);
  }

  ready(function () {
    // Mobile sidebar toggle
    var burger = document.querySelector(".esavest-burger");
    var sidebarToggle = document.getElementById("esavest-sidebar-toggle");
    var overlay = document.querySelector(".esavest-overlay");
    var body = document.body;

    if (burger && sidebarToggle) {
      burger.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();

        if (sidebarToggle.checked) {
          // Close sidebar
          sidebarToggle.checked = false;
          body.classList.remove("sidebar-open");
        } else {
          // Open sidebar
          sidebarToggle.checked = true;
          body.classList.add("sidebar-open");
        }
      });
    }

    if (overlay) {
      overlay.addEventListener("click", function () {
        if (sidebarToggle) {
          sidebarToggle.checked = false;
          body.classList.remove("sidebar-open");
        }
      });
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener("click", function (e) {
      if (
        window.innerWidth <= 768 &&
        sidebarToggle &&
        sidebarToggle.checked &&
        !e.target.closest(".esavest-sidebar") &&
        e.target !== burger &&
        !burger.contains(e.target)
      ) {
        sidebarToggle.checked = false;
        body.classList.remove("sidebar-open");
      }
    });

    // Close sidebar when pressing escape key
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && sidebarToggle && sidebarToggle.checked) {
        sidebarToggle.checked = false;
        body.classList.remove("sidebar-open");
      }
    });

    // Close sidebar when window is resized to desktop size
    function handleResize() {
      if (window.innerWidth > 768 && sidebarToggle && sidebarToggle.checked) {
        sidebarToggle.checked = false;
        body.classList.remove("sidebar-open");
      }
    }

    window.addEventListener("resize", handleResize);

    // Add active class to current nav item
    var currentPath = window.location.pathname + window.location.search;
    var navLinks = document.querySelectorAll(
      ".esavest-nav a, .esavest-dashboard-item"
    );

    navLinks.forEach(function (link) {
      if (link.href && currentPath.includes(new URL(link.href).search)) {
        link.classList.add("active");
      }
    });
  });
})();
