let lastUpdateAfter = null;
let autoRefreshTimer = null;
let currentRequest   = null;
let SCOPE_COLORS     = {};

function buildScopeColorMap(days) {
    const ids = [...new Set(days.flatMap(d => d.scopes.map(s => s.id)))];
    const map = Object.create(null);
    ids.forEach((id, idx) => {
        const hue = Math.round((idx * 137.508) % 360);
        map[id]= `hsl(${hue} 60% 85%)`;
    });
    return map;
}

function toMysql(date) {
    const d = (date instanceof Date) ? date : new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    const hours = String(d.getHours()).padStart(2, '0');
    const minutes = String(d.getMinutes()).padStart(2, '0');
    const seconds = String(d.getSeconds()).padStart(2, '0');
    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}
function isSameRequest(a, b) {
    if (!a || !b) return false;
    if (a.dateFrom !== b.dateFrom || a.dateUntil !== b.dateUntil) return false;
    return [...a.scopeIds].sort().join(',') === [...b.scopeIds].sort().join(',');
}

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('overall-calendar-form');
    if (form) form.addEventListener('submit', handleSubmit);

    const fromInput  = document.getElementById('calendar-date-from');
    const untilInput = document.getElementById('calendar-date-until');
    const todayIso   = new Date().toISOString().slice(0, 10);

    if (fromInput) {
        fromInput.min = todayIso;
        if (!fromInput.value) fromInput.value = todayIso;
    }
    if (untilInput) untilInput.min = todayIso;

    if (fromInput && untilInput) {
        fromInput.addEventListener('change', setUntilLimits);
        setUntilLimits();
    }

    function setUntilLimits() {
        if (!fromInput.value) { untilInput.min = untilInput.max = ''; return; }
        const fromDate = new Date(fromInput.value);
        const maxDate  = new Date(fromDate);
        let workdays   = 0;
        while (workdays < 4) {
            maxDate.setDate(maxDate.getDate() + 1);
            if (![0, 6].includes(maxDate.getDay())) workdays++;
        }
        untilInput.min = fromInput.value;
        untilInput.max = maxDate.toISOString().slice(0, 10);
        if (untilInput.value < untilInput.min || untilInput.value > untilInput.max) {
            untilInput.value = untilInput.min;
        }
    }

    const btn     = document.getElementById('calendar-fullscreen');
    const wrapper = document.querySelector('.overall-calendar-wrapper');

    if (btn && wrapper) {
        btn.addEventListener('click', () => {
            const isFull = wrapper.classList.toggle('fullscreen');
            btn.classList.toggle('is-active', isFull);
            btn.title = isFull ? 'Vollbild schließen' : 'Vollbild';
            togglePageScroll(isFull);
            if (isFull) btn.focus();
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
    }
});

async function handleSubmit(event) {
    event.preventDefault();

    const select = event.target.querySelector('select[name="scopes[]"]');
    const scopeIds = Array.from(select.selectedOptions).map(o => o.value);
    const dateFrom = event.target.querySelector('input[name="calendarDateFrom"]').value;
    const dateUntil = event.target.querySelector('input[name="calendarDateUntil"]').value;
    const errorBox = document.getElementById('scope-error');
    const errorMsg = errorBox.querySelector('.msg');

    if (!scopeIds.length) {
        errorMsg.textContent = 'Bitte mindestens einen Standort auswählen';
        errorBox.style.display = 'inline-flex';
        return;
    }
    errorBox.style.display = 'none';
    errorMsg.textContent = '';

    if (dateFrom && dateUntil) {
        let current = new Date(dateFrom), until = new Date(dateUntil), workdays = 0;
        while (current <= until) {
            if (![0, 6].includes(current.getDay())) workdays++;
            current.setDate(current.getDate() + 1);
        }
        if (workdays > 5) {
            alert('Bitte wählen Sie maximal 5 Werktage (Mo–Fr) aus.');
            return;
        }
    }

    const newRequest = {scopeIds, dateFrom, dateUntil};
    const incremental = isSameRequest(newRequest, currentRequest);

    const paramsObj = {
        scopeIds: scopeIds.join(','),
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
        currentRequest = newRequest;
        lastUpdateAfter = serverTs;
        startAutoRefresh();
    }
}

function startAutoRefresh() {
    if (autoRefreshTimer) clearInterval(autoRefreshTimer);
    let countdown = 60;
    autoRefreshTimer = setInterval(() => {
        if (--countdown <= 0) {
            countdown = 60;
            fetchIncrementalUpdate();
        }
    }, 1000);
}

async function fetchIncrementalUpdate() {
    if (!currentRequest || !lastUpdateAfter) return;
    const {scopeIds, dateFrom, dateUntil} = currentRequest;
    const params = new URLSearchParams({
        scopeIds: scopeIds.join(','),
        dateFrom,
        dateUntil,
        updateAfter: lastUpdateAfter
    });
    const res = await fetch(`overallcalendarData/?${params}`);
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
            scope.times.forEach(time => {
                time.seats.forEach((seat, idx) => {
                    const seatNo = idx + 1;
                    const cellId = `cell-${dateKey}-${time.name}-${scope.id}-${seatNo}`;
                    const cell = document.getElementById(cellId);
                    if (!cell || cell.dataset.status === seat.status) return;

                    cell.dataset.status = seat.status;
                    cell.className = `overall-calendar-seat overall-calendar-${seat.status}`;
                    cell.textContent = seat.status === 'termin' ? (seat.processId ?? '') : '';
                    const span = seat.status === 'termin' ? (seat.slots || 1) : 1;
                    cell.style.gridRow = `${cell.dataset.row} / span ${span}`;
                    if (seat.status === 'termin') {
                        cell.style.background = SCOPE_COLORS[scope.id];
                    } else {
                        cell.style.background = '';
                    }
                });
            });
        });
    });
}

function renderMultiDayCalendar(days) {
    const container = document.getElementById('overall-calendar');
    container.innerHTML = '';
    if (!days.length) {
        container.innerHTML = '<p>Keine Daten verfügbar.</p>';
        return;
    }

    SCOPE_COLORS = buildScopeColorMap(days);
    const allTimes = [...new Set(
        days.flatMap(day =>
            day.scopes.flatMap(scope => scope.times.map(t => t.name))
        )
    )].sort();

    const templateCols = ['max-content'];
    days.forEach((day, dayIdx) => {
        day.scopes.forEach((scope, scopeIdx) => {
            templateCols.push(`repeat(${scope.maxSeats}, minmax(32px, 1fr))`);
            if (scopeIdx < day.scopes.length - 1) templateCols.push('2px');
        });
        if (dayIdx < days.length - 1) templateCols.push('4px');
    });

    container.style.display = 'grid';
    container.style.gridTemplateColumns = templateCols.join(' ');
    container.style.minWidth = 'fit-content';

    addCell({
        text: 'Datum',
        className: 'overall-calendar-head overall-calendar-day-header overall-calendar-sticky-corner',
        row: 1, col: 1
    });

    let colCursor = 2;
    const totalRows = allTimes.length + 2;

    days.forEach((day, dayIdx) => {
        const totalSeatsForDay = day.scopes.reduce((sum, scope) => sum + scope.maxSeats, 0);
        const separatorsInDay = Math.max(0, day.scopes.length - 1);
        const daySpan = totalSeatsForDay + separatorsInDay;

        const date = new Date(day.date * 1000);
        const dayName = date.toLocaleDateString('de-DE', {weekday: 'short'});
        const dayDate = date.toLocaleDateString('de-DE', {day: '2-digit', month: '2-digit'});

        addCell({
            text: `${dayName} ${dayDate}`,
            className: 'overall-calendar-head overall-calendar-day-header overall-calendar-stick-top',
            row: 1,
            col: colCursor,
            colSpan: daySpan
        });

        colCursor += daySpan;
        if (dayIdx < days.length - 1) colCursor += 1;
    });

    addCell({
        text: 'Zeit',
        className: 'overall-calendar-head overall-calendar-scope-header overall-calendar-stick-left',
        row: 2,
        col: 1
    });

    colCursor = 2;
    days.forEach((day, dayIdx) => {
        day.scopes.forEach((scope, scopeIdx) => {
            const scopeStartCol = colCursor;

            const head= addCell({
                text: scope.shortName || scope.name || `Scope ${scope.id}`,
                className: 'overall-calendar-head overall-calendar-scope-header overall-calendar-stick-top',
                row: 2,
                col: scopeStartCol,
                colSpan: scope.maxSeats
            });
            head.style.background = SCOPE_COLORS[scope.id];

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
        const gridRow = timeIdx + 3;

        addCell({
            text: time,
            className: 'overall-calendar-time overall-calendar-stick-left',
            row: gridRow,
            col: 1
        });

        let col = 2;
        days.forEach((day, dayIdx) => {
            const dateKey = new Date(day.date * 1000).toISOString().slice(0, 10);
            day.scopes.forEach((scope, scopeIdx) => {
                const timeObj = scope.times.find(t => t.name === time) || {seats: []};

                for (let seatIdx = 0; seatIdx < scope.maxSeats; seatIdx++) {
                    if (occupied.has(`${gridRow}-${col}`)) { col++; continue; }

                    const seat= timeObj.seats[seatIdx] || {};
                    const status= seat.status ?? 'empty';
                    const cellId= `cell-${dateKey}-${time}-${scope.id}-${seatIdx + 1}`;

                    if (status === 'termin') {
                        const span = seat.slots || 1;
                        addCell({
                            text: seat.processId ?? '',
                            className: 'overall-calendar-seat overall-calendar-termin',
                            row: gridRow,
                            col,
                            rowSpan: span,
                            id: cellId,
                            dataStatus: 'termin'
                        });
                        for (let i = 0; i < span; i++) occupied.add(`${gridRow + i}-${col}`);
                    } else if (status !== 'skip') {
                        addCell({
                            className: `overall-calendar-seat overall-calendar-${status}`,
                            row: gridRow,
                            col,
                            id: cellId,
                            dataStatus: status
                        });
                    }
                    col++;
                }
                if (scopeIdx < day.scopes.length - 1) col++;
            });
            if (dayIdx < days.length - 1) col++;
        });
    });

    showFullscreenButton();

    function addCell({ text = '', className = '', row, col,
                         rowSpan = 1, colSpan = 1, id = null, dataStatus = null }) {
        const div = document.createElement('div');
        div.textContent = text;
        div.className = className;
        div.style.gridRow = `${row} / span ${rowSpan}`;
        div.style.gridColumn = `${col} / span ${colSpan}`;
        if (id) div.id = id;
        if (dataStatus) div.dataset.status = dataStatus;
        div.dataset.row = row;
        container.appendChild(div);
        return div;
    }
}

function showFullscreenButton() {
    const btn  = document.getElementById('calendar-fullscreen');
    const cal  = document.getElementById('overall-calendar');
    if (btn && cal && cal.children.length) btn.style.display = 'inline-block';
}
function togglePageScroll(disable) {
    document.documentElement.classList.toggle('no-page-scroll', disable);
    document.body.classList.toggle('no-page-scroll', disable);
}
