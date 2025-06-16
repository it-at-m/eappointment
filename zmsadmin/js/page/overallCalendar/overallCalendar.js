let lastUpdateAfter  = null;
let autoRefreshTimer = null;
let currentRequest   = null;

function toMysql(date) {
    const d = (date instanceof Date) ? date : new Date(date);
    return d.toISOString().slice(0, 19).replace('T', ' ');
}

function isSameRequest(a, b) {
    if (!a || !b) return false;
    if (a.dateFrom !== b.dateFrom || a.dateUntil !== b.dateUntil) return false;
    const x = [...a.scopeIds].sort().join(',');
    const y = [...b.scopeIds].sort().join(',');
    return x === y;
}

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('overall-calendar-form');
    if (form) {
        form.addEventListener('submit', handleSubmit);
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const fromInput  = document.getElementById('calendar-date-from');
    const untilInput = document.getElementById('calendar-date-until');

    if (fromInput && !fromInput.value) {
        const today = new Date();
        fromInput.value = today.toISOString().slice(0, 10);
    }

    if (fromInput && untilInput) {
        fromInput.addEventListener('change', setUntilLimits);
        setUntilLimits();

        function setUntilLimits() {
            if (fromInput.value) {
                let fromDate = new Date(fromInput.value);
                let maxDate  = new Date(fromDate);
                let daysAdded = 0;
                while (daysAdded < 4) {
                    maxDate.setDate(maxDate.getDate() + 1);
                    if (maxDate.getDay() !== 0 && maxDate.getDay() !== 6) {
                        daysAdded++;
                    }
                }
                untilInput.min = fromInput.value;
                untilInput.max = maxDate.toISOString().slice(0, 10);

                if (untilInput.value < untilInput.min || untilInput.value > untilInput.max) {
                    untilInput.value = untilInput.min;
                }
            } else {
                untilInput.min = '';
                untilInput.max = '';
            }
        }
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const btn     = document.getElementById('calendar-fullscreen');
    const wrapper = document.querySelector('.overall-calendar-wrapper');

    btn.addEventListener('click', () => {
        const isFull = wrapper.classList.toggle('fullscreen');
        btn.classList.toggle('is-active', isFull);
        btn.title = isFull ? 'Vollbild schließen' : 'Vollbild';

        if (isFull) btn.focus();
        togglePageScroll(isFull);
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && wrapper.classList.contains('fullscreen')) {
            wrapper.classList.remove('fullscreen');
            btn.classList.remove('is-active');
            btn.title = 'Vollbild';
            togglePageScroll(false);
            btn.focus();
        }
    });
});

async function handleSubmit(event) {
    event.preventDefault();

    const select   = event.target.querySelector('select[name="scopes[]"]');
    const scopeIds = Array.from(select.selectedOptions).map(o => o.value);
    const errorBox = document.getElementById('scope-error');
    const errorMsg = errorBox.querySelector('.msg');

    if (scopeIds.length === 0) {
        errorMsg.textContent = 'Bitte mindestens einen Standort auswählen';
        errorBox.style.display = 'inline-flex';
        return;
    } else {
        errorBox.style.display = 'none';
        errorMsg.textContent = '';
    }

    const dateFromInput  = event.target.querySelector('input[name="calendarDateFrom"]');
    const dateUntilInput = event.target.querySelector('input[name="calendarDateUntil"]');
    const dateFrom  = dateFromInput.value;
    const dateUntil = dateUntilInput.value;

    if (dateFrom && dateUntil) {
        let from  = new Date(dateFrom);
        let until = new Date(dateUntil);
        let workdays = 0;
        let current = new Date(from);
        while (current <= until) {
            const day = current.getDay();
            if (day !== 0 && day !== 6) { workdays++; }
            current.setDate(current.getDate() + 1);
        }
        if (workdays > 5) {
            alert('Bitte wählen Sie maximal 5 Werktage (Mo–Fr) aus.');
            return;
        }
    }

    const newRequest = { scopeIds, dateFrom, dateUntil };
    const incremental = isSameRequest(newRequest, currentRequest);

    const paramsObj = {
        scopeIds : scopeIds.join(','),
        dateFrom,
        dateUntil
    };
    if (incremental && lastUpdateAfter) {
        paramsObj.updateAfter = lastUpdateAfter;
    }
    const params = new URLSearchParams(paramsObj);

    const res = await fetch(`overallcalendarData/?${params.toString()}`);
    if (!res.ok) {
        alert('Fehler beim Laden des Kalenders!');
        return;
    }

    const data = await res.json();
    const serverTs = toMysql(res.headers.get('Last-Modified') || new Date());

    if (incremental) {
        const changes = data?.data?.days ?? [];
        if (changes.length) applyChanges(changes);
        lastUpdateAfter = serverTs;
    } else {
        renderMultiDayCalendar(data.data.days);
        currentRequest  = newRequest;
        lastUpdateAfter = serverTs;
        startAutoRefresh();
    }
}

function startAutoRefresh() {
    if (autoRefreshTimer) clearInterval(autoRefreshTimer);
    autoRefreshTimer = setInterval(fetchIncrementalUpdate, 60_000);
}

async function fetchIncrementalUpdate() {
    if (!currentRequest || !lastUpdateAfter) return;

    const { scopeIds, dateFrom, dateUntil } = currentRequest;
    const params = new URLSearchParams({
        scopeIds : scopeIds.join(','),
        dateFrom,
        dateUntil,
        updateAfter : lastUpdateAfter,
    });

    const res = await fetch(`overallcalendarData/?${params.toString()}`);
    if (!res.ok) return;

    lastUpdateAfter = toMysql(res.headers.get('Last-Modified') || new Date());

    const json = await res.json();
    const changes = json?.data?.days ?? [];
    if (changes.length) applyChanges(changes);
}

function applyChanges(days) {
    days.forEach(day => {
        const dateKey = new Date(day.date * 1000).toISOString().slice(0, 10);

        day.scopes.forEach(scope => {
            const scopeId = scope.id;

            scope.times.forEach(time => {
                const timeKey = time.name;

                time.seats.forEach((seat, idx) => {
                    const seatNo = idx + 1;
                    const cellId = `cell-${dateKey}-${timeKey}-${scopeId}-${seatNo}`;
                    const cell   = document.getElementById(cellId);
                    if (!cell) return;

                    if (cell.dataset.status === seat.status) return;
                    cell.dataset.status = seat.status;

                    cell.className = `overall-calendar-seat overall-calendar-${seat.status}`;
                    if (seat.status === 'termin') {
                        cell.textContent = seat.processId ?? '';
                    } else {
                        cell.textContent = '';
                    }

                    const rowStart = cell.dataset.row;
                    const span = (seat.status === 'termin') ? (seat.slots || 1) : 1;
                    cell.style.gridRow = `${rowStart} / span ${span}`;
                });
            });
        });
    });
}

function renderMultiDayCalendar(days) {
    const container = document.getElementById('overall-calendar');
    container.innerHTML = '';

    if (days.length === 0) {
        container.innerHTML = '<p>Keine Daten verfügbar.</p>';
        return;
    }

    if (days.length === 1) {
        const dateKey = new Date(days[0].date * 1000).toISOString().slice(0, 10);
        renderCalendar(days[0].scopes, dateKey);
        return;
    }

    const allTimes = Array.from(
        new Set(days.flatMap(day =>
            day.scopes.flatMap(s => s.times.map(t => t.name))
        ))
    ).sort();

    const templateCols = ['max-content'];

    days.forEach((day, dayIdx) => {
        day.scopes.forEach((scope, scopeIdx) => {
            templateCols.push(`repeat(${scope.maxSeats}, minmax(32px, 1fr))`);
            if (scopeIdx < day.scopes.length - 1) {
                templateCols.push('2px');
            }
        });
        if (dayIdx < days.length - 1) {
            templateCols.push('4px');
        }
    });

    container.style.display = 'grid';
    container.style.gridTemplateColumns = templateCols.join(' ');
    container.style.minWidth = 'fit-content';

    const totalRows = allTimes.length + 2;

    addCell({
        text: 'Datum',
        className: 'overall-calendar-head overall-calendar-day-header',
        row: 1,
        col: 1
    });

    let colCursor = 2;
    days.forEach((day, dayIdx) => {
        const dayScopes = day.scopes;
        const totalSeatsForDay = dayScopes.reduce((sum, scope) => sum + scope.maxSeats, 0);
        const separatorsInDay = Math.max(0, dayScopes.length - 1);
        const daySpan = totalSeatsForDay + separatorsInDay;

        const date = new Date(day.date * 1000);
        const dayName = date.toLocaleDateString('de-DE', { weekday: 'short' });
        const dayDate = date.toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit' });

        addCell({
            text: `${dayName} ${dayDate}`,
            className: 'overall-calendar-head overall-calendar-day-header',
            row: 1,
            col: colCursor,
            colSpan: daySpan
        });

        colCursor += daySpan;
        if (dayIdx < days.length - 1) colCursor += 1;
    });

    addCell({
        text: 'Zeit',
        className: 'overall-calendar-head overall-calendar-scope-header',
        row: 2,
        col: 1
    });

    colCursor = 2;
    days.forEach((day, dayIdx) => {
        day.scopes.forEach((scope, scopeIdx) => {
            addCell({
                text: scope.shortName || scope.name || `Scope ${scope.id}`,
                className: 'overall-calendar-head overall-calendar-scope-header',
                row: 2,
                col: colCursor,
                colSpan: scope.maxSeats
            });
            colCursor += scope.maxSeats;

            if (scopeIdx < day.scopes.length - 1) {
                addCell({
                    className: 'overall-calendar-separator',
                    row: 2,
                    col: colCursor,
                    rowSpan: allTimes.length + 1
                });
                colCursor += 1;
            }
        });

        if (dayIdx < days.length - 1) {
            addCell({
                className: 'overall-calendar-day-separator',
                row: 1,
                col: colCursor,
                rowSpan: totalRows
            });
            colCursor += 1;
        }
    });

    const occupied = new Set();

    allTimes.forEach((time, timeIdx) => {
        const gridRow = timeIdx + 3; // +3 wegen Datum- und Scope-Header

        addCell({
            text: time,
            className: 'overall-calendar-time',
            row: gridRow,
            col: 1
        });

        let col = 2;
        days.forEach((day, dayIdx) => {
            const dateKey = new Date(day.date * 1000).toISOString().slice(0,10);
            day.scopes.forEach((scope, scopeIdx) => {
                const timeObj = scope.times.find(t => t.name === time) || { seats: [] };

                for (let seatIdx = 0; seatIdx < scope.maxSeats; seatIdx++) {
                    const key = `${gridRow}-${col}`;
                    if (occupied.has(key)) {
                        col++;
                        continue;
                    }

                    const seat = timeObj.seats[seatIdx] || {};
                    const cls  = seat.status ?? 'empty';

                    if (cls === 'termin') {
                        const span = seat.slots || 1;
                        addCell({
                            text: seat.processId ?? '',
                            className: 'overall-calendar-seat overall-calendar-termin',
                            row: gridRow,
                            col,
                            rowSpan: span,
                            id: `cell-${dateKey}-${time}-${scope.id}-${seatIdx+1}`,
                            dataStatus: 'termin'
                        });
                        for (let i = 0; i < span; i++) {
                            occupied.add(`${gridRow + i}-${col}`);
                        }
                    } else if (cls !== 'skip') {
                        addCell({
                            className: `overall-calendar-seat overall-calendar-${cls}`,
                            row: gridRow,
                            col,
                            id: `cell-${dateKey}-${time}-${scope.id}-${seatIdx+1}`,
                            dataStatus: cls
                        });
                    }
                    col++;
                }

                if (scopeIdx < day.scopes.length - 1) {
                    col++; // Skip separator column
                }
            });

            if (dayIdx < days.length - 1) {
                col++; // Skip day separator column
            }
        });
    });

    function addCell({ text = '', className = '', row, col, rowSpan = 1, colSpan = 1, id = null, dataStatus = null }) {
        const div = document.createElement('div');
        div.textContent = text;
        div.className   = className;
        div.style.gridRow    = `${row} / span ${rowSpan}`;
        div.style.gridColumn = `${col} / span ${colSpan}`;
        if (id)         div.id            = id;
        if (dataStatus) div.dataset.status = dataStatus;
        div.dataset.row = row;
        container.appendChild(div);
    }

    showFullscreenButton();
}

function renderCalendar(scopes, dateKey) {
    const container = document.getElementById('overall-calendar');
    container.innerHTML = '';

    const allTimes = Array.from(
        new Set(scopes.flatMap(s => s.times.map(t => t.name)))
    ).sort();

    const templateCols = ['max-content'];
    scopes.forEach((scope, idx) => {
        templateCols.push(`repeat(${scope.maxSeats}, minmax(32px, 1fr))`);
        if (idx < scopes.length - 1) templateCols.push('2px');
    });
    container.style.display = 'grid';
    container.style.gridTemplateColumns = templateCols.join(' ');
    container.style.minWidth = 'fit-content';

    addCell({
        text: 'Zeit',
        className: 'overall-calendar-head',
        row: 1,
        col: 1
    });

    let colCursor = 2;
    scopes.forEach((scope, idx) => {
        addCell({
            text: scope.shortName || scope.name || `Scope ${scope.id}`,
            className: 'overall-calendar-head',
            row: 1,
            col: colCursor,
            colSpan: scope.maxSeats
        });
        colCursor += scope.maxSeats;

        if (idx < scopes.length - 1) {
            addCell({
                className: 'overall-calendar-separator',
                row: 1,
                col: colCursor,
                rowSpan: allTimes.length + 1
            });
            colCursor += 1;
        }
    });

    const occupied = new Set();

    allTimes.forEach((time, rowIdx) => {
        const gridRow = rowIdx + 2;

        addCell({
            text: time,
            className: 'overall-calendar-time',
            row: gridRow,
            col: 1
        });

        let col = 2;
        scopes.forEach((scope, scopeIdx) => {
            const timeObj = scope.times.find(t => t.name === time) || { seats: [] };

            for (let seatIdx = 0; seatIdx < scope.maxSeats; seatIdx++) {
                const key = `${gridRow}-${col}`;
                if (occupied.has(key)) { col++; continue; }

                const seat = timeObj.seats[seatIdx] || {};
                const cls  = seat.status ?? 'empty';

                if (cls === 'termin') {
                    const span = seat.slots || 1;
                    addCell({
                        text: seat.processId ?? '',
                        className: 'overall-calendar-seat overall-calendar-termin',
                        row: gridRow,
                        col,
                        rowSpan: span,
                        id: `cell-${dateKey}-${time}-${scope.id}-${seatIdx+1}`,
                        dataStatus: 'termin'
                    });
                    for (let i = 0; i < span; i++) occupied.add(`${gridRow + i}-${col}`);
                } else if (cls !== 'skip') {
                    addCell({
                        className: `overall-calendar-seat overall-calendar-${cls}`,
                        row: gridRow,
                        col,
                        id: `cell-${dateKey}-${time}-${scope.id}-${seatIdx+1}`,
                        dataStatus: cls
                    });
                }
                col++;
            }

            if (scopeIdx < scopes.length - 1) {
                addCell({
                    className: 'overall-calendar-separator',
                    row: gridRow,
                    col
                });
                col++;
            }
        });
    });

    function addCell({ text = '', className = '', row, col, rowSpan = 1, colSpan = 1, id = null, dataStatus = null }) {
        const div = document.createElement('div');
        div.textContent = text;
        div.className   = className;
        div.style.gridRow    = `${row} / span ${rowSpan}`;
        div.style.gridColumn = `${col} / span ${colSpan}`;
        if (id)         div.id            = id;
        if (dataStatus) div.dataset.status = dataStatus;
        div.dataset.row = row;
        container.appendChild(div);
    }

    showFullscreenButton();
}

function showFullscreenButton() {
    const fullscreenBtn = document.getElementById('calendar-fullscreen');
    const calendar = document.getElementById('overall-calendar');
    if (fullscreenBtn && calendar && calendar.children.length > 0) {
        fullscreenBtn.style.display = 'inline-block';
    }
}

function togglePageScroll(disable) {
    document.documentElement.classList.toggle('no-page-scroll', disable);
    document.body.classList.toggle('no-page-scroll', disable);
}
