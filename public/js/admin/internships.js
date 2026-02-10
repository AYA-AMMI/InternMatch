/**
 * =========================================
 * ADMIN INTERNSHIPS JAVASCRIPT
 * Filters, moderation, and internship details modal
 * =========================================
 */

document.addEventListener("DOMContentLoaded", function () {
    // =========================================
    // Filter Form Auto-Submit on Change
    // =========================================
    const filterSelects = document.querySelectorAll(".filters-form select");

    filterSelects.forEach((select) => {
        select.addEventListener("change", function () {
            // Auto-submit form when filter changes
            this.closest("form").submit();
        });
    });

    // =========================================
    // Search Form Enhancement
    // =========================================
    const searchInput = document.querySelector(
        '.filters-form input[name="search"]'
    );

    if (searchInput) {
        // Debounce search to avoid too many requests
        let searchTimeout;

        searchInput.addEventListener("input", function () {
            clearTimeout(searchTimeout);
            const form = this.closest("form");

            searchTimeout = setTimeout(() => {
                if (this.value.length >= 3 || this.value.length === 0) {
                    form.submit();
                }
            }, 500);
        });

        // Submit on Enter key
        searchInput.addEventListener("keypress", function (event) {
            if (event.key === "Enter") {
                event.preventDefault();
                this.closest("form").submit();
            }
        });
    }

    // =========================================
    // Table Row Hover Effect
    // =========================================
    const tableRows = document.querySelectorAll(".internships-table tbody tr");

    tableRows.forEach((row) => {
        row.addEventListener("mouseenter", function () {
            this.style.transform = "scale(1.005)";
            this.style.transition = "transform 0.2s ease";
        });

        row.addEventListener("mouseleave", function () {
            this.style.transform = "scale(1)";
        });
    });

    // =========================================
    // Confirmation Dialogs
    // =========================================
    const deleteButtons = document.querySelectorAll(
        'form[onsubmit*="confirm"] button[type="submit"]'
    );

    deleteButtons.forEach((button) => {
        button.addEventListener("click", function (event) {
            const form = this.closest("form");
            const message = form
                .getAttribute("onsubmit")
                .match(/confirm\('(.*)'\)/)[1];

            if (!confirm(message)) {
                event.preventDefault();
                event.stopPropagation();
                return false;
            }
        });
    });

    // =========================================
    // Filter Pills Display (Active Filters)
    // =========================================
    const currentStatus = new URLSearchParams(window.location.search).get(
        "status"
    );
    const currentIndustry = new URLSearchParams(window.location.search).get(
        "industry"
    );
    const currentSearch = new URLSearchParams(window.location.search).get(
        "search"
    );

    if (currentStatus || currentIndustry || currentSearch) {
        const resultsInfo = document.querySelector(".results-info");
        const pillsContainer = document.createElement("div");
        pillsContainer.className = "filter-pills mt-2 d-flex gap-2 flex-wrap";

        if (currentStatus) {
            pillsContainer.innerHTML += `
                <span class="badge bg-primary">
                    Status: ${currentStatus}
                    <i class="fas fa-times ms-1" style="cursor: pointer;" onclick="removeFilter('status')"></i>
                </span>
            `;
        }

        if (currentIndustry) {
            pillsContainer.innerHTML += `
                <span class="badge bg-primary">
                    Industry: ${currentIndustry}
                    <i class="fas fa-times ms-1" style="cursor: pointer;" onclick="removeFilter('industry')"></i>
                </span>
            `;
        }

        if (currentSearch) {
            pillsContainer.innerHTML += `
                <span class="badge bg-primary">
                    Search: "${currentSearch}"
                    <i class="fas fa-times ms-1" style="cursor: pointer;" onclick="removeFilter('search')"></i>
                </span>
            `;
        }

        resultsInfo.appendChild(pillsContainer);
    }

    console.log(
        "%c💼 Internships Management Loaded",
        "color: #10b981; font-size: 14px; font-weight: bold;"
    );
});

/**
 * =========================================
 * REMOVE FILTER PILL
 * Remove specific filter and reload page
 * =========================================
 */
function removeFilter(filterName) {
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.delete(filterName);
    window.location.search = urlParams.toString();
}

/**
 * =========================================
 * SHOW INTERNSHIP DETAILS (Modal)
 * Called from template onclick
 * =========================================
 */
function showInternshipDetails(internshipId) {
    const modal = new bootstrap.Modal(
        document.getElementById("internshipDetailsModal")
    );
    const modalBody = document.getElementById("internshipDetailsBody");

    // Get internship data from hidden div
    const dataElement = document.querySelector(
        `#internshipsData [data-id="${internshipId}"]`
    );

    if (!dataElement) {
        modalBody.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Error!</strong> Internship data not found.
            </div>
        `;
        modal.show();
        return;
    }

    // Extract data attributes
    const title = dataElement.getAttribute("data-title");
    const description = dataElement.getAttribute("data-description");
    const skills = JSON.parse(dataElement.getAttribute("data-skills") || "[]");
    const location = dataElement.getAttribute("data-location");
    const duration = dataElement.getAttribute("data-duration");
    const salary = dataElement.getAttribute("data-salary");

    // Build skills HTML
    const skillsHTML =
        skills && skills.length > 0
            ? skills
                  .map((skill) => `<span class="skill-tag">${skill}</span>`)
                  .join(" ")
            : '<span class="text-muted">No specific skills required</span>';

    // Build salary HTML
    const salaryHTML =
        salary && salary !== "null"
            ? `$${parseFloat(salary).toFixed(2)}`
            : '<span class="text-muted">Not specified</span>';

    // Display details
    modalBody.innerHTML = `
        <div class="internship-details">
            <div class="detail-row">
                <div class="detail-label"><i class="fas fa-briefcase"></i> Title:</div>
                <div class="detail-value"><strong>${title}</strong></div>
            </div>
            <div class="detail-row">
                <div class="detail-label"><i class="fas fa-align-left"></i> Description:</div>
                <div class="detail-value">
                    <p style="max-height: 200px; overflow-y: auto; white-space: pre-wrap;">${description}</p>
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-label"><i class="fas fa-cogs"></i> Required Skills:</div>
                <div class="detail-value">${skillsHTML}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label"><i class="fas fa-map-marker-alt"></i> Location:</div>
                <div class="detail-value">${location}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label"><i class="fas fa-clock"></i> Duration:</div>
                <div class="detail-value">${duration} months</div>
            </div>
            <div class="detail-row">
                <div class="detail-label"><i class="fas fa-dollar-sign"></i> Salary:</div>
                <div class="detail-value">${salaryHTML}</div>
            </div>
        </div>
    `;

    modal.show();
}

/**
 * =========================================
 * EXPORT FILTERED RESULTS (Optional)
 * Export current filtered internships to CSV
 * =========================================
 */
function exportFilteredInternships() {
    // Get current filter parameters
    const params = new URLSearchParams(window.location.search);

    // Create CSV content
    let csv =
        "ID,Title,Company,Industry,Location,Duration,Status,Applications,Posted Date\n";

    // Get all visible table rows
    const rows = document.querySelectorAll(".internships-table tbody tr");

    rows.forEach((row) => {
        const cells = row.querySelectorAll("td");
        const rowData = [
            cells[0].textContent.trim(), // ID
            cells[1].textContent.trim().split("\n")[0], // Title (first line only)
            cells[2].textContent.trim().split("\n")[0], // Company
            cells[3].textContent.trim(), // Industry
            cells[4].textContent.trim(), // Location
            cells[5].textContent.trim(), // Duration
            cells[6].textContent.trim(), // Status
            cells[7].textContent.trim(), // Applications
            cells[8].textContent.trim(), // Posted Date
        ];

        // Escape and format CSV row
        const csvRow = rowData
            .map((cell) => `"${cell.replace(/"/g, '""')}"`)
            .join(",");
        csv += csvRow + "\n";
    });

    // Create download link
    const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);

    link.setAttribute("href", url);
    link.setAttribute(
        "download",
        `internships_${new Date().toISOString().split("T")[0]}.csv`
    );
    link.style.visibility = "hidden";

    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    console.log("Exported filtered internships to CSV");
}
