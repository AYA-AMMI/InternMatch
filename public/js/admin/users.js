/**
 * =========================================
 * ADMIN USERS JAVASCRIPT
 * User details modals and search functionality
 * =========================================
 */

document.addEventListener("DOMContentLoaded", function () {
    // =========================================
    // Tab Persistence (Remember Active Tab)
    // =========================================
    const tabLinks = document.querySelectorAll(
        '#usersTabs button[data-bs-toggle="tab"]'
    );
    const activeTab =
        localStorage.getItem("adminUsersActiveTab") || "students-tab";

    // Activate saved tab
    const savedTab = document.getElementById(activeTab);
    if (savedTab) {
        const tab = new bootstrap.Tab(savedTab);
        tab.show();
    }

    // Save active tab on change
    tabLinks.forEach((tab) => {
        tab.addEventListener("shown.bs.tab", function (event) {
            localStorage.setItem("adminUsersActiveTab", event.target.id);
        });
    });

    // =========================================
    // Search Form Enhancement
    // =========================================
    const searchForms = document.querySelectorAll(".search-form");

    searchForms.forEach((form) => {
        const input = form.querySelector('input[type="text"]');

        if (input) {
            // Auto-submit on Enter key
            input.addEventListener("keypress", function (event) {
                if (event.key === "Enter") {
                    event.preventDefault();
                    form.submit();
                }
            });

            // Clear button functionality
            const clearBtn = form.querySelector(".btn-secondary");
            if (clearBtn) {
                clearBtn.addEventListener("click", function (event) {
                    event.preventDefault();
                    input.value = "";
                    form.submit();
                });
            }
        }
    });

    // =========================================
    // Table Row Hover Effect
    // =========================================
    const tableRows = document.querySelectorAll(".users-table tbody tr");

    tableRows.forEach((row) => {
        row.addEventListener("mouseenter", function () {
            this.style.transform = "scale(1.01)";
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

    console.log(
        "%c👥 Users Management Loaded",
        "color: #8b5cf6; font-size: 14px; font-weight: bold;"
    );
});

/**
 * =========================================
 * VIEW STUDENT DETAILS (Modal)
 * Called from template onclick
 * =========================================
 */
function viewStudentDetails(studentId) {
    const modal = new bootstrap.Modal(
        document.getElementById("studentDetailsModal")
    );
    const modalBody = document.getElementById("studentDetailsBody");

    // Show loading spinner
    modalBody.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mt-3">Loading student details...</p>
        </div>
    `;

    modal.show();

    // Fetch student details via AJAX
    fetch(`/admin/user/student/${studentId}`)
        .then((response) => {
            if (!response.ok) {
                throw new Error("Network response was not ok");
            }
            return response.json();
        })
        .then((data) => {
            // Build details HTML
            const skillsHTML =
                data.skills && data.skills.length > 0
                    ? data.skills
                          .map(
                              (skill) =>
                                  `<span class="skill-tag">${skill}</span>`
                          )
                          .join(" ")
                    : '<span class="text-muted">No skills listed</span>';

            const bioHTML = data.bio
                ? `<p>${data.bio}</p>`
                : '<p class="text-muted">No bio provided</p>';

            modalBody.innerHTML = `
                <div class="student-details">
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-id-badge"></i> Student ID:</div>
                        <div class="detail-value">#${data.id}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-user"></i> Full Name:</div>
                        <div class="detail-value">${data.firstName} ${
                data.lastName
            }</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-envelope"></i> Email:</div>
                        <div class="detail-value"><a href="mailto:${
                            data.email
                        }">${data.email}</a></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-phone"></i> Phone:</div>
                        <div class="detail-value">${
                            data.phone ||
                            '<span class="text-muted">Not provided</span>'
                        }</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-map-marker-alt"></i> Expected Location:</div>
                        <div class="detail-value">${
                            data.expectedLocation ||
                            '<span class="text-muted">Not specified</span>'
                        }</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-clock"></i> Expected Duration:</div>
                        <div class="detail-value">${
                            data.expectedDuration
                                ? data.expectedDuration + " months"
                                : '<span class="text-muted">Not specified</span>'
                        }</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-cogs"></i> Skills:</div>
                        <div class="detail-value">${skillsHTML}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-info-circle"></i> Bio:</div>
                        <div class="detail-value">${bioHTML}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-file-alt"></i> Total Applications:</div>
                        <div class="detail-value"><span class="badge badge-info">${
                            data.totalApplications
                        }</span></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-calendar-plus"></i> Registered:</div>
                        <div class="detail-value">${new Date(
                            data.createdAt
                        ).toLocaleDateString("en-US", {
                            year: "numeric",
                            month: "long",
                            day: "numeric",
                            hour: "2-digit",
                            minute: "2-digit",
                        })}</div>
                    </div>
                </div>
            `;
        })
        .catch((error) => {
            console.error("Error fetching student details:", error);
            modalBody.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Error!</strong> Failed to load student details. Please try again.
                </div>
            `;
        });
}

/**
 * =========================================
 * VIEW COMPANY DETAILS (Modal)
 * Called from template onclick
 * =========================================
 */
function viewCompanyDetails(companyId) {
    const modal = new bootstrap.Modal(
        document.getElementById("companyDetailsModal")
    );
    const modalBody = document.getElementById("companyDetailsBody");

    // Show loading spinner
    modalBody.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted mt-3">Loading company details...</p>
        </div>
    `;

    modal.show();

    // Fetch company details via AJAX
    fetch(`/admin/user/company/${companyId}`)
        .then((response) => {
            if (!response.ok) {
                throw new Error("Network response was not ok");
            }
            return response.json();
        })
        .then((data) => {
            // Build details HTML
            const descriptionHTML = data.description
                ? `<p>${data.description}</p>`
                : '<p class="text-muted">No description provided</p>';

            const websiteHTML = data.website
                ? `<a href="${data.website}" target="_blank">${data.website} <i class="fas fa-external-link-alt"></i></a>`
                : '<span class="text-muted">Not provided</span>';

            const verifiedBadge = data.isVerified
                ? '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Verified</span>'
                : '<span class="badge badge-warning"><i class="fas fa-clock"></i> Unverified</span>';

            modalBody.innerHTML = `
                <div class="company-details">
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-id-badge"></i> Company ID:</div>
                        <div class="detail-value">#${data.id}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-building"></i> Company Name:</div>
                        <div class="detail-value"><strong>${
                            data.companyName
                        }</strong></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-envelope"></i> Email:</div>
                        <div class="detail-value"><a href="mailto:${
                            data.email
                        }">${data.email}</a></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-industry"></i> Industry:</div>
                        <div class="detail-value"><span class="industry-badge">${
                            data.industry
                        }</span></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-map-marker-alt"></i> Location:</div>
                        <div class="detail-value">${data.location}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-globe"></i> Website:</div>
                        <div class="detail-value">${websiteHTML}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-check-circle"></i> Verification Status:</div>
                        <div class="detail-value">${verifiedBadge}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-info-circle"></i> Description:</div>
                        <div class="detail-value">${descriptionHTML}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-briefcase"></i> Total Internships:</div>
                        <div class="detail-value"><span class="badge badge-info">${
                            data.totalInternships
                        }</span></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label"><i class="fas fa-calendar-plus"></i> Registered:</div>
                        <div class="detail-value">${new Date(
                            data.createdAt
                        ).toLocaleDateString("en-US", {
                            year: "numeric",
                            month: "long",
                            day: "numeric",
                            hour: "2-digit",
                            minute: "2-digit",
                        })}</div>
                    </div>
                </div>
            `;
        })
        .catch((error) => {
            console.error("Error fetching company details:", error);
            modalBody.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Error!</strong> Failed to load company details. Please try again.
                </div>
            `;
        });
}
