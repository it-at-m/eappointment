let calendarCache = [];
let lastUpdateAfter = null;
let autoRefreshTimer = null;
let currentRequest = null;
let SCOPE_COLORS = {};
let CLOSURES = new Set();

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

async function fetchClosures({scopeIds, dateFrom, dateUntil, fullReload = true}) {
    const res = await fetch(`closureData/?${new URLSearchParams({
        scopeIds: scopeIds.join(','),
        dateFrom,
        dateUntil
    })}`);
    if (!res.ok) throw new Error('Fehler beim Laden der Closures');
    const { data } = await res.json();
    const set = new Set();
    for (const it of (data?.items || [])) {
        set.add(`${it.date}|${it.scopeId}`);
    }
    CLOSURES = set;
}

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('overall-calendar-form');
    const btnRefresh = document.getElementById('refresh-calendar');
    const fromInput = document.getElementById('calendar-date-from');
    const untilInput = document.getElementById('calendar-date-until');
    const todayIso = new Date().toISOString().slice(0, 10);

    if (fromInput) {
        fromInput.min = todayIso;
        if (!fromInput.value) fromInput.value = todayIso;
    }
    if (untilInput) untilInput.min = todayIso;

    if (fromInput && untilInput) {
        fromInput.addEventListener('change', setUntilLimits);
        setUntilLimits();
    }

    if (form) form.addEventListener('submit', handleSubmit);

    if (btnRefresh) {
        btnRefresh.addEventListener('click', async () => {
            if (!currentRequest) return;
            const {scopeIds, dateFrom, dateUntil} = currentRequest;
            try {
                await Promise.all([
                    fetchCalendar({ scopeIds, dateFrom, dateUntil, fullReload: false }),
                    fetchClosures({ scopeIds, dateFrom, dateUntil })
                ]);
                renderMultiDayCalendar(calendarCache);
            } catch (e) {
                alert('Fehler beim Aktualisieren: ' + e.message);
            }
        });
    }

    const fsBtn = document.getElementById('calendar-fullscreen');
    const wrapper = document.querySelector('.overall-calendar-wrapper');
    if (fsBtn && wrapper) {
        fsBtn.addEventListener('click', () => {
            const isFull = wrapper.classList.toggle('fullscreen');
            fsBtn.classList.toggle('is-active', isFull);
            fsBtn.title = isFull ? 'Vollbild schließen' : 'Vollbild';
            togglePageScroll(isFull);
            if (isFull) fsBtn.focus();
        });
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && wrapper.classList.contains('fullscreen')) {
                wrapper.classList.remove('fullscreen');
                fsBtn.classList.remove('is-active');
                fsBtn.title = 'Vollbild';
                togglePageScroll(false);
                fsBtn.focus();
            }
        });
    }

    function setUntilLimits() {
        if (!fromInput.value) { untilInput.min = untilInput.max = ''; return; }
        const fromDate = new Date(fromInput.value);
        const maxDate = new Date(fromDate);
        let workdays = 0;
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
});

async function fetchCalendar({scopeIds, dateFrom, dateUntil, fullReload = false}) {
    const incremental = !fullReload && isSameRequest({scopeIds, dateFrom, dateUntil}, currentRequest);
    const paramsObj = {scopeIds: scopeIds.join(','), dateFrom, dateUntil};
    if (incremental && lastUpdateAfter) {
        paramsObj.updateAfter = lastUpdateAfter;
    }
    const params = new URLSearchParams(paramsObj);

    const res = await fetch(`overallcalendarData/?${params}`);
    if (!res.ok) throw new Error('Fehler beim Laden des Kalenders');
    const json = await res.json();
    const serverTs = toMysql(res.headers.get('Last-Modified') || new Date());

    if (incremental) {
        mergeDelta(json.data.days);
    } else {
        calendarCache = json.data.days;
    }

    currentRequest = {scopeIds, dateFrom, dateUntil};
    lastUpdateAfter = serverTs;
}

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

    try {
        await Promise.all([
            fetchCalendar({ scopeIds, dateFrom, dateUntil, fullReload: true }),
            fetchClosures({ scopeIds, dateFrom, dateUntil })
        ]);
        renderMultiDayCalendar(calendarCache);
        startAutoRefresh();
    } catch (e) {
        alert(e.message);
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
    const { scopeIds, dateFrom, dateUntil } = currentRequest;

    const res = await fetch(`overallcalendarData/?${new URLSearchParams({
        scopeIds: scopeIds.join(','),
        dateFrom,
        dateUntil,
        updateAfter: lastUpdateAfter
    })}`);
    if (res.ok) {
        lastUpdateAfter = toMysql(res.headers.get('Last-Modified') || new Date());
        const json = await res.json();
        mergeDelta(json.data.days);
    }

    await fetchClosures({ scopeIds, dateFrom, dateUntil });
    renderMultiDayCalendar(calendarCache);
}

function mergeDelta(deltaDays) {
    if (!deltaDays?.length) return;
    deltaDays.forEach(dDay => {
        let fullDay = calendarCache.find(cd => cd.date === dDay.date);
        if (!fullDay) {
            calendarCache.push(structuredClone(dDay));
            return;
        }
        dDay.scopes.forEach(dScope => {
            let fullScope = fullDay.scopes.find(s => s.id === dScope.id);
            if (!fullScope) {
                fullDay.scopes.push(structuredClone(dScope));
                return;
            }
            fullScope.maxSeats = Math.max(fullScope.maxSeats || 1, dScope.maxSeats || 1);
            dScope.times.forEach(dTime => {
                let fullTime = fullScope.times.find(t => t.name === dTime.name);
                if (!fullTime) {
                    fullScope.times.push(structuredClone(dTime));
                    return;
                }
                dTime.seats.forEach(seatDelta => {
                    if (!seatDelta || typeof seatDelta !== 'object') return;
                    const idx = (seatDelta.seatNo ?? 1) - 1;
                    fullTime.seats[idx] = structuredClone(seatDelta);
                    if (fullTime.seats.length <= idx) fullTime.seats.length = idx + 1;
                });
            });
        });
    });
}

function structuredClone(obj) {
    return window.structuredClone
        ? window.structuredClone(obj)
        : JSON.parse(JSON.stringify(obj));
}

function renderMultiDayCalendar(days) {
    const container = document.getElementById('overall-calendar');
    container.innerHTML = '';
    if (!days.length) {
        container.innerHTML = '<p>Keine Daten verfügbar.</p>';
        return;
    }

    const ymdLocalFromUnix = (ts) => new Date(ts * 1000).toLocaleDateString('sv-SE');

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

    const addCell = ({text = '', className = '', row, col, rowSpan = 1, colSpan = 1, id = null, dataStatus = null}) => {
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
    };

    addCell({
        text: 'Datum',
        className: 'overall-calendar-head overall-calendar-day-header overall-calendar-sticky-corner',
        row: 1, col: 1
    });

    let colCursor = 2, totalRows = allTimes.length + 2;
    days.forEach((day, dayIdx) => {
        const seatsInDay = day.scopes.reduce((sum, s) => sum + s.maxSeats, 0);
        const separators = Math.max(0, day.scopes.length - 1);
        const daySpan = seatsInDay + separators;
        const dateObj = new Date(day.date * 1000);
        const dayLabel = dateObj.toLocaleDateString('de-DE', {weekday: 'short', day: '2-digit', month: '2-digit'});

        addCell({
            text: dayLabel,
            className: 'overall-calendar-head overall-calendar-day-header overall-calendar-stick-top',
            row: 1, col: colCursor, colSpan: daySpan
        });

        colCursor += daySpan;
        if (dayIdx < days.length - 1) colCursor += 1;
    });

    addCell({
        text: 'Zeit',
        className: 'overall-calendar-head overall-calendar-scope-header overall-calendar-stick-left',
        row: 2, col: 1
    });

    colCursor = 2;
    days.forEach((day, dayIdx) => {
        const dateIsoForDay = new Date(day.date * 1000).toLocaleDateString('sv-SE');
        day.scopes.forEach((scope, scopeIdx) => {
            const head = addCell({
                text: scope.shortName || scope.name || `Scope ${scope.id}`,
                className: 'overall-calendar-head overall-calendar-scope-header overall-calendar-stick-top',
                row: 2, col: colCursor, colSpan: scope.maxSeats
            });
            head.style.background = SCOPE_COLORS[scope.id];
            if (isScopeClosed(dateIsoForDay, scope.id)) {
                head.classList.add('is-closed');
            }
            colCursor += scope.maxSeats;
            if (scopeIdx < day.scopes.length - 1) {
                addCell({
                    className: 'overall-calendar-separator',
                    row: 2, col: colCursor, rowSpan: totalRows - 1
                });
                colCursor++;
            }
        });
        if (dayIdx < days.length - 1) {
            addCell({
                className: 'overall-calendar-day-separator',
                row: 1, col: colCursor, rowSpan: totalRows
            });
            colCursor++;
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
            const dateKey = ymdLocalFromUnix(day.date);
            day.scopes.forEach((scope, scopeIdx) => {
                const timeObj = scope.times.find(t => t.name === time) || {seats: []};
                const closed = isScopeClosed(dateKey, scope.id);
                for (let seatIdx = 0; seatIdx < scope.maxSeats; seatIdx++) {
                    if (occupied.has(`${gridRow}-${col}`)) { col++; continue; }

                    const seat= timeObj.seats[seatIdx] || {};
                    const status= seat.status ?? 'empty';
                    const cellId= `cell-${dateKey}-${time}-${scope.id}-${seatIdx + 1}`;

                    if (status === 'termin') {
                        const span = seat.slots || 1;
                        const cell = addCell({
                            text: seat.processId ?? '',
                            className: 'overall-calendar-seat overall-calendar-termin',
                            row: gridRow,
                            col,
                            rowSpan: span,
                            id: cellId,
                            dataStatus: 'termin'
                        });
                        cell.style.background = SCOPE_COLORS[scope.id];
                        for (let i = 0; i < span; i++) occupied.add(`${gridRow + i}-${col}`);
                    } else if (status !== 'skip') {
                        addCell({
                            className: `overall-calendar-seat overall-calendar-${status}${closed ? ' overall-calendar-closed' : ''}`,
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

    const fsBtn = document.getElementById('calendar-fullscreen');
    if (fsBtn && container.children.length) fsBtn.style.display = 'inline-block';
}

function togglePageScroll(disable) {
    document.documentElement.classList.toggle('no-page-scroll', disable);
    document.body.classList.toggle('no-page-scroll', disable);
}

function isScopeClosed(dateIso, scopeId) {
    return CLOSURES.has(`${dateIso}|${scopeId}`);
}