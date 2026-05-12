(function () {
    var dataNode = document.getElementById('bbapp-dashboard-data');

    if (!dataNode || typeof Chart === 'undefined') {
        return;
    }

    var data = JSON.parse(dataNode.textContent);
    var gridColor = 'rgba(36, 50, 66, 0.09)';
    var textColor = '#243242';
    var colors = {
        blue: '#1e6fff',
        teal: '#13a89e',
        amber: '#f0a72f',
        coral: '#f06449',
        ink: '#243242',
        lilac: '#7b61ff'
    };

    Chart.defaults.font.family = "'Trebuchet MS', 'Verdana', sans-serif";
    Chart.defaults.color = textColor;
    Chart.defaults.plugins.legend.labels.boxWidth = 12;
    Chart.defaults.plugins.legend.labels.boxHeight = 12;

    function canvas(name) {
        return document.querySelector('[data-bbapp-chart="' + name + '"]');
    }

    function numberValue(value) {
        return Number(value || 0);
    }

    function lineOptions() {
        return {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'bottom' } },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: gridColor } }
            }
        };
    }

    function doughnutOptions() {
        return {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            plugins: { legend: { position: 'bottom' } }
        };
    }

    if (canvas('profiles')) {
        new Chart(canvas('profiles'), {
            type: 'line',
            data: {
                labels: data.profiles.labels,
                datasets: [
                    {
                        label: 'New users',
                        data: data.profiles.newUsers.map(numberValue),
                        borderColor: colors.teal,
                        backgroundColor: 'rgba(19, 168, 158, 0.12)',
                        tension: 0.35,
                        fill: true
                    },
                    {
                        label: 'Total profiles',
                        data: data.profiles.cumulativeUsers.map(numberValue),
                        borderColor: colors.blue,
                        backgroundColor: 'rgba(30, 111, 255, 0.08)',
                        tension: 0.35,
                        fill: true
                    }
                ]
            },
            options: lineOptions()
        });
    }

    if (canvas('dailyActiveUsers')) {
        new Chart(canvas('dailyActiveUsers'), {
            type: 'bar',
            data: {
                labels: data.dailyActiveUsers.labels,
                datasets: [{
                    label: 'Daily active users',
                    data: data.dailyActiveUsers.values.map(numberValue),
                    backgroundColor: 'rgba(30, 111, 255, 0.82)',
                    borderRadius: 8
                }]
            },
            options: lineOptions()
        });
    }

    if (canvas('themeMode')) {
        new Chart(canvas('themeMode'), {
            type: 'doughnut',
            data: {
                labels: data.themeMode.map(function (item) { return item.label; }),
                datasets: [{
                    data: data.themeMode.map(function (item) { return numberValue(item.value); }),
                    backgroundColor: [colors.ink, colors.amber, colors.teal],
                    borderWidth: 0
                }]
            },
            options: doughnutOptions()
        });
    }

    if (canvas('premiumStatus')) {
        new Chart(canvas('premiumStatus'), {
            type: 'pie',
            data: {
                labels: data.premiumStatus.map(function (item) { return item.label; }),
                datasets: [{
                    data: data.premiumStatus.map(function (item) { return numberValue(item.value); }),
                    backgroundColor: [colors.teal, colors.blue, colors.amber, colors.coral],
                    borderWidth: 0
                }]
            },
            options: doughnutOptions()
        });
    }

    if (canvas('playbackByType')) {
        var typeLabels = {
            video: 'Videos',
            sound: 'Sounds',
            flashcards: 'Flash cards',
            quick_calm: 'Quick Calm'
        };
        var typeColors = {
            video: colors.blue,
            sound: colors.teal,
            flashcards: colors.amber,
            quick_calm: colors.coral
        };
        var firstType = data.playbackByType.video || Object.values(data.playbackByType)[0] || { labels: [] };

        new Chart(canvas('playbackByType'), {
            type: 'line',
            data: {
                labels: firstType.labels,
                datasets: Object.keys(data.playbackByType).map(function (type) {
                    return {
                        label: typeLabels[type] || type,
                        data: data.playbackByType[type].values.map(numberValue),
                        borderColor: typeColors[type] || colors.lilac,
                        backgroundColor: 'transparent',
                        tension: 0.35
                    };
                })
            },
            options: lineOptions()
        });
    }

    if (canvas('selectedProfilePlays') && data.selectedProfile && data.selectedProfile.playsOverTime) {
        new Chart(canvas('selectedProfilePlays'), {
            type: 'line',
            data: {
                labels: data.selectedProfile.playsOverTime.labels,
                datasets: [{
                    label: 'Plays',
                    data: data.selectedProfile.playsOverTime.values.map(numberValue),
                    borderColor: colors.coral,
                    backgroundColor: 'rgba(240, 100, 73, 0.12)',
                    tension: 0.35,
                    fill: true
                }]
            },
            options: lineOptions()
        });
    }

    if (canvas('selectedProfileTypes') && data.selectedProfile && data.selectedProfile.eventsByType) {
        new Chart(canvas('selectedProfileTypes'), {
            type: 'doughnut',
            data: {
                labels: data.selectedProfile.eventsByType.map(function (item) {
                    return (typeLabels[item.label] || item.label || 'Unknown').replace('_', ' ');
                }),
                datasets: [{
                    data: data.selectedProfile.eventsByType.map(function (item) { return numberValue(item.value); }),
                    backgroundColor: [colors.blue, colors.teal, colors.amber, colors.coral, colors.lilac],
                    borderWidth: 0
                }]
            },
            options: doughnutOptions()
        });
    }

    var search = document.querySelector('[data-bbapp-table-search]');
    var tables = Array.prototype.slice.call(document.querySelectorAll('[data-bbapp-table]'));
    var sortableTables = Array.prototype.slice.call(document.querySelectorAll('[data-bbapp-sortable]'));

    if (search) {
        search.addEventListener('input', function () {
            var term = search.value.trim().toLowerCase();

            tables.forEach(function (table) {
                Array.prototype.slice.call(table.querySelectorAll('tbody tr')).forEach(function (row) {
                    row.hidden = term !== '' && row.textContent.toLowerCase().indexOf(term) === -1;
                });
            });
        });
    }

    sortableTables.forEach(function (table) {
        var headers = Array.prototype.slice.call(table.querySelectorAll('thead th'));
        var tbody = table.querySelector('tbody');

        headers.forEach(function (header, columnIndex) {
            header.tabIndex = 0;
            header.setAttribute('role', 'button');
            header.setAttribute('aria-sort', 'none');
            header.classList.add('is-sortable');

            function sortTable() {
                var currentDirection = header.getAttribute('aria-sort') === 'ascending' ? 'descending' : 'ascending';
                var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));

                headers.forEach(function (item) {
                    item.setAttribute('aria-sort', 'none');
                    item.classList.remove('is-sorted-asc', 'is-sorted-desc');
                });

                rows.sort(function (a, b) {
                    var aCell = a.children[columnIndex];
                    var bCell = b.children[columnIndex];
                    var aValue = aCell ? aCell.textContent.trim() : '';
                    var bValue = bCell ? bCell.textContent.trim() : '';
                    var aParsed = parseSortValue(aValue);
                    var bParsed = parseSortValue(bValue);
                    var result;

                    if (aParsed.type === 'number' && bParsed.type === 'number') {
                        result = aParsed.value - bParsed.value;
                    } else if (aParsed.type === 'date' && bParsed.type === 'date') {
                        result = aParsed.value - bParsed.value;
                    } else {
                        result = aValue.localeCompare(bValue, undefined, { numeric: true, sensitivity: 'base' });
                    }

                    return currentDirection === 'ascending' ? result : result * -1;
                });

                rows.forEach(function (row) {
                    tbody.appendChild(row);
                });

                header.setAttribute('aria-sort', currentDirection);
                header.classList.add(currentDirection === 'ascending' ? 'is-sorted-asc' : 'is-sorted-desc');
            }

            header.addEventListener('click', sortTable);
            header.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    sortTable();
                }
            });
        });
    });

    function parseSortValue(value) {
        var normalized = value.replace(/,/g, '');
        var number = Number(normalized);
        var date = Date.parse(value.replace('Never', ''));

        if (value !== '' && Number.isFinite(number)) {
            return { type: 'number', value: number };
        }

        if (value !== 'Never' && Number.isFinite(date)) {
            return { type: 'date', value: date };
        }

        if (value === 'Never') {
            return { type: 'date', value: 0 };
        }

        return { type: 'text', value: value.toLowerCase() };
    }
}());
