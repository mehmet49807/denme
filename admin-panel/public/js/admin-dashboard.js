(function () {
    const config = window.adminDashboardConfig;
    if (!config || typeof Chart === 'undefined') return;

    const palette = {
        violet: '#7C3AED',
        magenta: '#EC4899',
        coral: '#FF6B8A',
        gold: '#FBBF24',
        blue: '#6366F1',
        muted: '#C4B5FD',
    };

    let charts = {};

    function buildCharts(data) {
        const chartsData = data.charts;
        const labels = chartsData.labels;

        if (charts.signups) {
            charts.signups.data.labels = labels;
            charts.signups.data.datasets[0].data = chartsData.user_signups;
            charts.signups.update('none');
        } else {
            charts.signups = new Chart(document.getElementById('chartUserSignups'), {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'Yeni üye',
                        data: chartsData.user_signups,
                        borderColor: palette.violet,
                        backgroundColor: 'rgba(124, 58, 237, 0.12)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                    }],
                },
                options: chartLineOptions(),
            });
        }

        if (charts.gender) {
            charts.gender.data.datasets[0].data = [
                chartsData.gender.male,
                chartsData.gender.female,
                chartsData.gender.banned,
            ];
            charts.gender.update('none');
        } else {
            charts.gender = new Chart(document.getElementById('chartGender'), {
                type: 'doughnut',
                data: {
                    labels: ['Aktif Erkek', 'Aktif Kadın', 'Banlı'],
                    datasets: [{
                        data: [
                            chartsData.gender.male,
                            chartsData.gender.female,
                            chartsData.gender.banned,
                        ],
                        backgroundColor: [palette.blue, palette.coral, palette.muted],
                        borderWidth: 0,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    animation: { duration: 600 },
                },
            });
        }

        if (charts.genderBar) {
            charts.genderBar.data.datasets[0].data = [
                chartsData.gender.male,
                chartsData.gender.female,
            ];
            charts.genderBar.update('none');
        } else {
            charts.genderBar = new Chart(document.getElementById('chartGenderBar'), {
                type: 'bar',
                data: {
                    labels: ['Erkek', 'Kadın'],
                    datasets: [{
                        label: 'Aktif kullanıcı',
                        data: [chartsData.gender.male, chartsData.gender.female],
                        backgroundColor: [palette.blue, palette.magenta],
                        borderRadius: 8,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } },
                    },
                    animation: { duration: 600 },
                },
            });
        }

        if (charts.messages) {
            charts.messages.data.labels = labels;
            charts.messages.data.datasets[0].data = chartsData.messages;
            charts.messages.update('none');
        } else {
            charts.messages = new Chart(document.getElementById('chartMessages'), {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'Mesaj',
                        data: chartsData.messages,
                        borderColor: palette.magenta,
                        backgroundColor: 'rgba(236, 72, 153, 0.1)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                    }],
                },
                options: chartLineOptions(),
            });
        }

        if (charts.premium) {
            charts.premium.data.labels = labels;
            charts.premium.data.datasets[0].data = chartsData.premium_sales;
            charts.premium.update('none');
        } else {
            charts.premium = new Chart(document.getElementById('chartPremium'), {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'Premium satış',
                        data: chartsData.premium_sales,
                        backgroundColor: palette.gold,
                        borderRadius: 6,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, ticks: { precision: 0 } },
                    },
                    animation: { duration: 600 },
                },
            });
        }

        buildOnlineChart(data.online);
    }

    function buildOnlineChart(online) {
        if (!online || !document.getElementById('chartOnlineDaily')) return;

        const labels = online.daily_labels || [];
        const values = online.daily || [];

        if (charts.onlineDaily) {
            charts.onlineDaily.data.labels = labels;
            charts.onlineDaily.data.datasets[0].data = values;
            charts.onlineDaily.update('none');
        } else {
            charts.onlineDaily = new Chart(document.getElementById('chartOnlineDaily'), {
                type: 'line',
                data: {
                    labels,
                    datasets: [{
                        label: 'Aktif kullanıcı',
                        data: values,
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.12)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                        pointBackgroundColor: '#10B981',
                    }],
                },
                options: chartLineOptions(),
            });
        }
    }

    function chartLineOptions() {
        return {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } },
            },
            animation: { duration: 600 },
        };
    }

    function updateStatCards(stats) {
        const map = {
            statTotalUsers: stats.total_users,
            statTotalReferrals: stats.total_referrals,
            statReferredUsers: stats.referred_users,
            statActiveMale: stats.active_male,
            statActiveFemale: stats.active_female,
            statPendingReports: stats.pending_reports,
            statActivePremium: stats.active_premium,
            statSignupsToday: stats.signups_today,
            statPendingProfiles: stats.pending_profiles,
            statAiFlags: stats.ai_flags,
            statOpenSupport: stats.open_support,
        };

        Object.entries(map).forEach(function ([id, value]) {
            const el = document.getElementById(id);
            if (el) el.textContent = value;
        });

        const revenue = document.getElementById('statRevenue');
        if (revenue) {
            revenue.innerHTML = Number(stats.revenue_tl).toLocaleString('tr-TR') + ' <small>TL</small>';
        }

        const legendMale = document.getElementById('legendMale');
        const legendFemale = document.getElementById('legendFemale');
        if (legendMale) legendMale.textContent = stats.active_male;
        if (legendFemale) legendFemale.textContent = stats.active_female;
    }

    function updateOnlineCard(online) {
        if (!online) return;

        const map = {
            onlineNowCount: online.now,
            onlineNowMale: online.now_male,
            onlineNowFemale: online.now_female,
            onlineToday: online.periods?.today,
            onlineYesterday: online.periods?.yesterday,
            onlineThisWeek: online.periods?.this_week,
            onlineLastWeek: online.periods?.last_week,
            onlineThisMonth: online.periods?.this_month,
            onlineLastMonth: online.periods?.last_month,
        };

        Object.entries(map).forEach(function ([id, value]) {
            const el = document.getElementById(id);
            if (el && value !== undefined) el.textContent = value;
        });

        buildOnlineChart(online);
    }

    const REFRESH_MS = 60000;
    const liveIndicator = document.querySelector('.admin-live-indicator');
    let isRefreshing = false;

    function setUpdatedAt(value) {
        const updated = document.getElementById('adminDashboardUpdatedAt');
        if (updated) updated.textContent = value;
    }

    function applyPayload(payload) {
        updateStatCards(payload.stats);
        buildCharts(payload);
        updateOnlineCard(payload.online);
        setUpdatedAt(payload.updated_at || new Date().toLocaleString('tr-TR'));
    }

    async function refresh() {
        if (isRefreshing) return;
        isRefreshing = true;
        liveIndicator?.classList.add('admin-live-indicator--refreshing');

        try {
            const res = await fetch(config.statsUrl, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                cache: 'no-store',
            });
            if (!res.ok) return;
            const json = await res.json();
            if (json.success && json.data) applyPayload(json.data);
        } catch (e) {
            /* silent */
        } finally {
            isRefreshing = false;
            liveIndicator?.classList.remove('admin-live-indicator--refreshing');
        }
    }

    applyPayload({
        stats: config.initial.stats,
        charts: config.initial.charts,
        online: config.initial.online,
        updated_at: new Date().toLocaleString('tr-TR'),
    });

    setTimeout(refresh, 1500);
    setInterval(refresh, REFRESH_MS);
})();
