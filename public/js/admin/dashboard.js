/**
 * =========================================
 * ADMIN DASHBOARD JAVASCRIPT
 * Chart.js initialization and data visualization
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
    // PIE CHART: Application Status Distribution
    // =========================================
    const applicationStatusCanvas = document.getElementById(
        "applicationStatusChart"
    );

    if (
        applicationStatusCanvas &&
        typeof applicationStatusData !== "undefined"
    ) {
        new Chart(applicationStatusCanvas, {
            type: "pie",
            data: {
                labels: ["Pending", "Accepted", "Rejected"],
                datasets: [
                    {
                        data: [
                            applicationStatusData.pending,
                            applicationStatusData.accepted,
                            applicationStatusData.rejected,
                        ],
                        backgroundColor: [
                            "#fbbf24", // Pending (amber)
                            "#10b981", // Accepted (green)
                            "#ef4444", // Rejected (red)
                        ],
                        borderColor: "#ffffff",
                        borderWidth: 3,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: "bottom",
                        labels: {
                            padding: 15,
                            font: {
                                size: 13,
                            },
                        },
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                const label = context.label || "";
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce(
                                    (a, b) => a + b,
                                    0
                                );
                                const percentage = (
                                    (value / total) *
                                    100
                                ).toFixed(1);
                                return `${label}: ${value} (${percentage}%)`;
                            },
                        },
                    },
                },
            },
        });
    }

    // =========================================
    // BAR CHART: Internships by Industry
    // =========================================
    const internshipsByIndustryCanvas = document.getElementById(
        "internshipsByIndustryChart"
    );

    if (
        internshipsByIndustryCanvas &&
        typeof internshipsByIndustry !== "undefined"
    ) {
        const industries = Object.keys(internshipsByIndustry);
        const counts = Object.values(internshipsByIndustry);

        new Chart(internshipsByIndustryCanvas, {
            type: "bar",
            data: {
                labels: industries,
                datasets: [
                    {
                        label: "Number of Internships",
                        data: counts,
                        backgroundColor: "#3b82f6",
                        borderColor: "#2563eb",
                        borderWidth: 0,
                        borderRadius: 8,
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
                            stepSize: 1,
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
                        display: false,
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return `Internships: ${context.parsed.y}`;
                            },
                        },
                    },
                },
            },
        });
    }

    // =========================================
    // LINE CHART: Registration Evolution
    // =========================================
    const registrationEvolutionCanvas = document.getElementById(
        "registrationEvolutionChart"
    );

    if (
        registrationEvolutionCanvas &&
        typeof registrationStats !== "undefined"
    ) {
        const months = Object.keys(registrationStats);
        const studentCounts = months.map(
            (month) => registrationStats[month].students
        );
        const companyCounts = months.map(
            (month) => registrationStats[month].companies
        );

        new Chart(registrationEvolutionCanvas, {
            type: "line",
            data: {
                labels: months,
                datasets: [
                    {
                        label: "Students",
                        data: studentCounts,
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
                        label: "Companies",
                        data: companyCounts,
                        borderColor: "#8b5cf6",
                        backgroundColor: "rgba(139, 92, 246, 0.1)",
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 5,
                        pointHoverRadius: 7,
                        pointBackgroundColor: "#8b5cf6",
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
                            stepSize: 1,
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
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return `${context.dataset.label}: ${context.parsed.y}`;
                            },
                        },
                    },
                },
            },
        });
    }

    // =========================================
    // Stats Cards Animation on Scroll
    // =========================================
    const statCards = document.querySelectorAll(".stat-card");

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

    statCards.forEach((card) => {
        card.style.opacity = "0";
        card.style.transform = "translateY(20px)";
        card.style.transition = "all 0.6s ease";
        observer.observe(card);
    });

    // =========================================
    // Animate Stat Values (Count Up Effect)
    // =========================================
    const statValues = document.querySelectorAll(".stat-value");

    statValues.forEach((stat) => {
        const finalValue = parseInt(stat.textContent);
        let currentValue = 0;
        const increment = Math.ceil(finalValue / 50);
        const duration = 1000;
        const stepTime = duration / (finalValue / increment);

        const timer = setInterval(() => {
            currentValue += increment;
            if (currentValue >= finalValue) {
                stat.textContent = finalValue;
                clearInterval(timer);
            } else {
                stat.textContent = currentValue;
            }
        }, stepTime);
    });

    console.log(
        "%c Dashboard Charts Initialized",
        "color:#10b981; font-size: 14px; font-weight: bold;"
    );
});
