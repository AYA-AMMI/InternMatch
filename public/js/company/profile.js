document.addEventListener("DOMContentLoaded", function () {
    // Description character counter
    const descriptionTextarea = document.getElementById("description");
    const charCount = document.querySelector("small.text-muted");

    descriptionTextarea?.addEventListener("input", function () {
        const length = this.value.length;
        charCount.textContent = `${length}/1000 characters`;

        if (length > 1000) {
            this.value = this.value.substring(0, 1000);
        }
    });

    // Unsaved changes warning
    let formChanged = false;
    const form = document.getElementById("profileForm");
    const formInputs = form.querySelectorAll("input, textarea, select");

    formInputs.forEach((input) => {
        input.addEventListener("change", function () {
            formChanged = true;
        });
    });

    window.addEventListener("beforeunload", function (e) {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = "";
        }
    });

    form.addEventListener("submit", function () {
        formChanged = false;
    });
});
