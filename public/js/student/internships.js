document.addEventListener("DOMContentLoaded", function () {
const filterForm = document.getElementById("filterForm");
const applyFiltersBtn = document.getElementById("applyFilters");
const resetFiltersBtn = document.getElementById("resetFilters");
const internshipsList = document.getElementById("internshipsList");
const internshipItems = document.querySelectorAll(".internship-item");
const countSpan = document.getElementById("count");

// Apply filters
applyFiltersBtn.addEventListener("click", function () {
    const industry = document.getElementById("industry").value;
    const location = document.getElementById("location").value;
    const duration = document.getElementById("duration").value;
    const sortBy = document.getElementById("sortBy").value;

    let visibleCount = 0;
    let itemsArray = Array.from(internshipItems);

    // Filter items
    itemsArray.forEach((item) => {
        const itemIndustry = item.dataset.industry;
        const itemLocation = item.dataset.location;
        const itemDuration = item.dataset.duration;

        let matches = true;

        if (industry && itemIndustry !== industry) matches = false;
        if (location && itemLocation !== location) matches = false;
        if (duration && itemDuration !== duration) matches = false;

        if (matches) {
            item.style.display = "block";
            visibleCount++;
        } else {
            item.style.display = "none";
        }
    });

    // Sort items
    if (sortBy === "match") {
        itemsArray.sort((a, b) => {
            return (
                parseInt(b.dataset.score) - parseInt(a.dataset.score)
            );
        });
    } else if (sortBy === "duration") {
        itemsArray.sort((a, b) => {
            return (
                parseInt(a.dataset.duration) -
                parseInt(b.dataset.duration)
            );
        });
    }

    // Reorder DOM
    itemsArray.forEach((item) => internshipsList.appendChild(item));

    // Update count
    countSpan.textContent = visibleCount;

    // Show message if no results
    const existingAlert = document.querySelector(".no-results-alert");
    if (existingAlert) existingAlert.remove();

    if (visibleCount === 0) {
        const alert = document.createElement("div");
        alert.className =
            "alert alert-warning no-results-alert fade-in";
        alert.innerHTML =
            '<i class="fas fa-exclamation-triangle me-2"></i>No internships match your filters. Try adjusting your criteria.';
        internshipsList.parentNode.insertBefore(alert, internshipsList);
    }
});

// Reset filters
resetFiltersBtn.addEventListener("click", function () {
    filterForm.reset();
    internshipItems.forEach((item) => {
        item.style.display = "block";
    });
    countSpan.textContent = internshipItems.length;

    const existingAlert = document.querySelector(".no-results-alert");
    if (existingAlert) existingAlert.remove();
});
});