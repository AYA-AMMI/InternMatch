/**
 * =========================================
 * ADMIN STATISTICS JAVASCRIPT
 * Advanced charts and data visualization
 * =========================================
 */

document.addEventListener("DOMContentLoaded", function () {
    // =========================================
    // Chart.js Global Configuration
    // =========================================
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = "#64748b";
    Chart.defaults.plugins.legend.display = true;
    Chart.defaults.plugins.legend.position = "bottom";
    Chart.defaults.plugins.tooltip.backgroundColor = "rgba(30, 41, 59, 0.95)";
    Chart.defaults.plugins.tooltip.padding = 12;
    Chart.defaults.plugins.tooltip.cornerRadius = 8;
    Chart.defaults.plugins.tooltip.titleColor = "#f8fafc";
    Chart.defaults.plugins.tooltip.bodyColor = "#cbd5e1";

    // =========================================
    // BAR CHART: Top Industries
    // Shows internships AND applications for each industry
    // =========================================
    const topIndustriesCanvas = document.getElementById("topIndustriesChart");

    if (topIndustriesCanvas && typeof topIndustriesData !== "undefined") {
        const industries = Object.keys(topIndustriesData);
        const internshipCounts = industries.map(
            (ind) => topIndustriesData[ind].internships
        );
        const applicationCounts = industries.map(
            (ind) => topIndustriesData[ind].applications
        );

        new Chart(topIndustriesCanvas, {
            type: "bar",
            data: {
                labels: industries,
                datasets: [
                    {
                        label: "Internships",
                        data: internshipCounts,
                        backgroundColor: "#3b82f6",
                        borderRadius: 6,
                        barPercentage: 0.7,
                    },
                    {
                        label: "Applications",
                        data: applicationCounts,
                        backgroundColor: "#8b5cf6",
                        borderRadius: 6,
                        barPercentage: 0.7,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 5,
                            font: {
                                size: 12,
                            },
                        },
                        grid: {
                            color: "#f1f5f9",
                            drawBorder: false,
                        },
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 12,
                            },
                            maxRotation: 45,
                            minRotation: 45,
                        },
                        grid: {
                            display: false,
                        },
                    },
                },
                plugins: {
                    legend: {
                        position: "bottom",
                        labels: {
                            padding: 15,
                            font: {
                                size: 13,
                            },
                            usePointStyle: true,
                        },
                    },
                },
            },
        });
    }

    // =========================================
    // HISTOGRAM: Match Score Distribution
    // Shows how many applications fall in each score range
    // =========================================
    const matchScoreCanvas = document.getElementById(
        "matchScoreDistributionChart"
    );

    if (matchScoreCanvas && typeof matchScoreDistribution !== "undefined") {
        const ranges = Object.keys(matchScoreDistribution);
        const counts = Object.values(matchScoreDistribution);

        // Color gradient from red to green based on score
        const colors = [
            "#ef4444", // 0-20: Red
            "#f59e0b", // 21-40: Orange
            "#fbbf24", // 41-60: Yellow
            "#10b981", // 61-80: Light Green
            "#059669", // 81-100: Dark Green
        ];

        new Chart(matchScoreCanvas, {
            type: "bar",
            data: {
                labels: ranges,
                datasets: [
                    {
                        label: "Number of Applications",
                        data: counts,
                        backgroundColor: colors,
                        borderRadius: 6,
                        barPercentage: 0.8,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 5,
                            font: {
                                size: 12,
                            },
                        },
                        grid: {
                            color: "#f1f5f9",
                            drawBorder: false,
                        },
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 12,
                            },
                        },
                        grid: {
                            display: false,
                        },
                    },
                },
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return `Applications: ${context.parsed.y}`;
                            },
                            title: function (context) {
                                return `Match Score: ${context[0].label}`;
                            },
                        },
                    },
                },
            },
        });
    }

    // =========================================
    // LINE CHART: Monthly Trends
    // Shows applications and internships posted over time
    // =========================================
    const monthlyTrendsCanvas = document.getElementById("monthlyTrendsChart");

    if (monthlyTrendsCanvas && typeof monthlyTrendsData !== "undefined") {
        const months = Object.keys(monthlyTrendsData);
        const applicationCounts = months.map(
            (month) => monthlyTrendsData[month].applications
        );
        const internshipCounts = months.map(
            (month) => monthlyTrendsData[month].internships
        );

        new Chart(monthlyTrendsCanvas, {
            type: "line",
            data: {
                labels: months,
                datasets: [
                    {
                        label: "Applications",
                        data: applicationCounts,
                        borderColor: "#3b82f6",
                        backgroundColor: "rgba(59, 130, 246, 0.1)",
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        pointBackgroundColor: "#3b82f6",
                        pointBorderColor: "#ffffff",
                        pointBorderWidth: 2,
                    },
                    {
                        label: "Internships Posted",
                        data: internshipCounts,
                        borderColor: "#10b981",
                        backgroundColor: "rgba(16, 185, 129, 0.1)",
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        pointBackgroundColor: "#10b981",
                        pointBorderColor: "#ffffff",
                        pointBorderWidth: 2,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                interaction: {
                    mode: "index",
                    intersect: false,
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 5,
                            font: {
                                size: 12,
                            },
                        },
                        grid: {
                            color: "#f1f5f9",
                            drawBorder: false,
                        },
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 12,
                            },
                        },
                        grid: {
                            display: false,
                        },
                    },
                },
                plugins: {
                    legend: {
                        position: "bottom",
                        labels: {
                            padding: 15,
                            font: {
                                size: 13,
                            },
                            usePointStyle: true,
                        },
                    },
                },
            },
        });
    }

    // =========================================
    // Metric Cards Animation
    // =========================================
    const metricCards = document.querySelectorAll(".metric-card");

    const observerOptions = {
        threshold: 0.1,
        rootMargin: "0px 0px -50px 0px",
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = "1";
                    entry.target.style.transform = "translateY(0)";
                }, index * 100);
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    metricCards.forEach((card) => {
        card.style.opacity = "0";
        card.style.transform = "translateY(20px)";
        card.style.transition = "all 0.6s ease";
        observer.observe(card);
    });

    // =========================================
    // Animate Metric Values (Count Up Effect)
    // =========================================
    const metricValues = document.querySelectorAll(".metric-value");

    metricValues.forEach((metric) => {
        const text = metric.textContent;

        // Check if value contains % or is a number
        const hasPercent = text.includes("%");
        const numericValue = parseFloat(text);

        if (!isNaN(numericValue)) {
            let currentValue = 0;
            const increment = numericValue / 50;
            const duration = 1000;
            const stepTime = duration / 50;

            const timer = setInterval(() => {
                currentValue += increment;
                if (currentValue >= numericValue) {
                    metric.textContent = hasPercent
                        ? `${numericValue.toFixed(2)}%`
                        : Math.round(numericValue);
                    clearInterval(timer);
                } else {
                    metric.textContent = hasPercent
                        ? `${currentValue.toFixed(2)}%`
                        : Math.round(currentValue);
                }
            }, stepTime);
        }
    });

    // =========================================
    // Table Row Highlighting
    // =========================================
    const tableRows = document.querySelectorAll(".data-table-card tbody tr");

    tableRows.forEach((row) => {
        row.addEventListener("mouseenter", function () {
            this.style.transform = "scale(1.02)";
            this.style.transition = "transform 0.2s ease";
        });

        row.addEventListener("mouseleave", function () {
            this.style.transform = "scale(1)";
        });
    });

    // =========================================
    // Export Button Enhancement
    // =========================================
    const exportBtn = document.querySelector('a[href*="statistics/export"]');

    if (exportBtn) {
        exportBtn.addEventListener("click", function (event) {
            // Show loading indicator
            const originalText = this.innerHTML;
            this.innerHTML =
                '<i class="fas fa-spinner fa-spin"></i> Exporting...';
            this.classList.add("disabled");

            // Re-enable after 2 seconds
            setTimeout(() => {
                this.innerHTML = originalText;
                this.classList.remove("disabled");
            }, 2000);
        });
    }

    console.log(
        "%c📊 Advanced Statistics Loaded",
        "color: #8b5cf6; font-size: 14px; font-weight: bold;"
    );
});

/**
 * =========================================
 * PRINT STATISTICS REPORT (Optional)
 * =========================================
 */
function printStatisticsReport() {
    window.print();
}

/**
 * =========================================
 * SHARE STATISTICS (Optional)
 * Copy current page URL to clipboard
 * =========================================
 */
function shareStatistics() {
    navigator.clipboard
        .writeText(window.location.href)
        .then(() => {
            alert("Statistics page URL copied to clipboard!");
        })
        .catch((err) => {
            console.error("Failed to copy URL:", err);
        });
}
