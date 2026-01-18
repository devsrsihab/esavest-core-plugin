document.addEventListener("DOMContentLoaded", () => {
  // =============================
  // MATERIAL CARD ACTIVE + UNIT
  // =============================
  const cards = document.querySelectorAll(".esavest-material-card");
  const unitInput = document.querySelector('input[name="request_unit"]');

  cards.forEach((card) => {
    card.addEventListener("click", () => {
      cards.forEach((c) => c.classList.remove("is-active"));
      card.classList.add("is-active");

      const radio = card.querySelector('input[name="material_id"]');
      if (radio) {
        radio.checked = true;
        if (radio.dataset.unit && unitInput) {
          unitInput.value = radio.dataset.unit;
        }
      }
    });
  });

  // =============================
  // FILE PREVIEW LOGIC
  // =============================
  const fileInput = document.getElementById("esavest_request_file");
  const imgPreview = document.getElementById("esavest-image-preview");
  const imgTag = imgPreview ? imgPreview.querySelector("img") : null;
  const fileNameBox = document.getElementById("esavest-file-name");

  if (fileInput) {
    fileInput.addEventListener("change", function () {
      imgPreview.style.display = "none";
      fileNameBox.style.display = "none";
      imgTag.src = "";

      const file = this.files[0];
      if (!file) return;

      const mime = file.type;

      // ✅ IMAGE PREVIEW
      if (mime.startsWith("image/")) {
        const reader = new FileReader();
        reader.onload = function (e) {
          imgTag.src = e.target.result;
          imgPreview.style.display = "block";
        };
        reader.readAsDataURL(file);
      }
      // ❌ NON-IMAGE (PDF, EXCEL, DOC)
      else {
        fileNameBox.innerText = "Selected file: " + file.name;
        fileNameBox.style.display = "block";
      }
    });
  }
});
