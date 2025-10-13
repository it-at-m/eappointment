let calendarCache = [];
let calendarMeta = null;
let lastUpdateAfter = null;
let autoRefreshTimer = null;
let currentRequest = null;
let SCOPE_COLORS = {};
let CLOSURES = new Set();
const STEP_MIN = 5;

function buildScopeColorMap(days) {
    const ids = [...new Set(days.flatMap(d => d.scopes.map(s => s.id)))];
    const map = Object.create(null);
    ids.forEach((id, idx) => {
        const hue = Math.round((idx * 137.508) % 360);
        map[id] = `hsl(${hue} 60% 85%)`;
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

async function fetchClosures({scopeIds, dateFrom, dateUntil}) {
    const res = await fetch(`closureData/?${new URLSearchParams({
        scopeIds: scopeIds.join(','),
        dateFrom,
        dateUntil
    })}`);
    if (!res.ok) throw new Error('Fehler beim Laden der Closures');
    const {data} = await res.json();
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
                    fetchCalendar({scopeIds, dateFrom, dateUntil, fullReload: false}),
                    fetchClosures({scopeIds, dateFrom, dateUntil})
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
        if (!fromInput.value) {
            untilInput.min = untilInput.max = '';
            return;
        }
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
    const paramsObj = {scopeIds: scopeIds.join(','), dateFrom, dateUntil};
    if (!fullReload && lastUpdateAfter) {
        paramsObj.updateAfter = lastUpdateAfter;
    }

    const res = await fetch(`overallcalendarData/?${new URLSearchParams(paramsObj)}`);
    if (!res.ok) throw new Error('Fehler beim Laden des Kalenders');
    const json = await res.json();
    calendarMeta = json.data.meta || calendarMeta;
    const serverTs = json.data.maxUpdatedAt || toMysql(new Date());

    if (fullReload) {
        calendarCache = json.data.days;
    } else {
        mergeDelta(json.data.days, json.data.deletedProcessIds || []);
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
            fetchCalendar({scopeIds, dateFrom, dateUntil, fullReload: true}),
            fetchClosures({scopeIds, dateFrom, dateUntil})
        ]);
        renderMultiDayCalendar(calendarCache);
        startAutoRefresh();
    } catch (e) {
        alert('Fehler beim Laden' + e.message);
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

    const res = await fetch(`overallcalendarData/?${new URLSearchParams({
        scopeIds: scopeIds.join(','), dateFrom, dateUntil, updateAfter: lastUpdateAfter
    })}`);
    if (res.ok) {
        const json = await res.json();
        calendarMeta = json.data.meta || calendarMeta;
        lastUpdateAfter = json.data.maxUpdatedAt || toMysql(new Date());
        mergeDelta(json.data.days, json.data.deletedProcessIds || []);
    }

    await fetchClosures({scopeIds, dateFrom, dateUntil});
    renderMultiDayCalendar(calendarCache);
}

function mergeDelta(deltaDays, deletedProcessIds = []) {
    if (Array.isArray(deletedProcessIds) && deletedProcessIds.length) {
        const dead = new Set(deletedProcessIds.map(Number));
        for (const day of calendarCache) {
            for (const scope of day.scopes) {
                if (!Array.isArray(scope.events)) continue;
                scope.events = scope.events.filter(e => !dead.has(e.processId));
            }
        }
    }
    if (!deltaDays?.length) {
        sortCalendarCache();
        return;
    }

    for (const dDay of deltaDays) {
        let fullDay = calendarCache.find(cd => cd.date === dDay.date);
        if (!fullDay) {
            fullDay = {date: dDay.date, scopes: []};
            calendarCache.push(fullDay);
        }

        for (const dScope of (dDay.scopes || [])) {
            let fullScope = fullDay.scopes.find(s => s.id === dScope.id);
            if (!fullScope) {
                fullScope = {id: dScope.id, intervals: [], events: []};
                fullDay.scopes.push(fullScope);
            }
            if (Array.isArray(dScope.intervals)) {
                fullScope.intervals = dScope.intervals.map(iv => ({...iv}));
            }
        }
    }

    const latestByPid = new Map();
    for (const dDay of deltaDays) {
        for (const dScope of (dDay.scopes || [])) {
            for (const event of (dScope.events || [])) {
                if (!event || typeof event !== 'object') continue;
                const key = event.processId;
                const cand = {...event, __day: dDay.date, __scope: dScope.id};
                const prev = latestByPid.get(key);
                if (!prev || (event.updatedAt && event.updatedAt >= prev.updatedAt)) {
                    latestByPid.set(key, cand);
                }
            }
        }
    }

    if (!latestByPid.size) {
        sortCalendarCache();
        return;
    }

    const affected = new Set(latestByPid.keys());
    for (const day of calendarCache) {
        for (const scope of day.scopes) {
            if (!Array.isArray(scope.events)) continue;
            scope.events = scope.events.filter(e => !affected.has(e.processId));
        }
    }

    for (const ev of latestByPid.values()) {
        if (ev.status !== 'confirmed') continue;

        let day = calendarCache.find(d => d.date === ev.__day);
        if (!day) {
            day = {date: ev.__day, scopes: []};
            calendarCache.push(day);
        }

        let scope = day.scopes.find(s => s.id === ev.__scope);
        if (!scope) {
            scope = {id: ev.__scope, intervals: [], events: []};
            day.scopes.push(scope);
        }

        if (!Array.isArray(scope.events)) scope.events = [];
        scope.events.push({
            processId: ev.processId, start: ev.start, end: ev.end,
            status: 'confirmed', updatedAt: ev.updatedAt
        });
    }

    sortCalendarCache();
}

function sortCalendarCache() {
    calendarCache.sort((a, b) => String(a.date).localeCompare(String(b.date)));

    for (const day of calendarCache) {
        day.scopes.sort((a, b) => (a.id ?? 0) - (b.id ?? 0));
        for (const scope of day.scopes) {
            if (!Array.isArray(scope.events)) continue;
            scope.events.sort(
                (a, b) =>
                    String(a.start).localeCompare(String(b.start)) ||
                    (a.processId - b.processId)
            );
        }
    }
}

function renderMultiDayCalendar(days) {
    const container = document.getElementById('overall-calendar');

    const noData =
        !Array.isArray(days) ||
        days.length === 0 ||
        !calendarMeta ||
        !calendarMeta.axis ||
        typeof calendarMeta.axis.start !== 'string' ||
        typeof calendarMeta.axis.end !== 'string';

    if (noData) {
        const parent = container.parentNode;
        const next = container.cloneNode(false);
        const oldId = container.id;

        next.removeAttribute('id');
        const p = document.createElement('p');
        p.textContent = 'Keine Daten verfügbar.';
        next.appendChild(p);

        parent.insertBefore(next, container);

        container.id = oldId + '__old__' + Date.now();
        next.id = oldId;

        container.style.display = 'none';
        deferredRemove(container);
        return;
    }


    const axis = calendarMeta.axis;
    const allTimes = buildTimeAxis(axis);
    SCOPE_COLORS = buildScopeColorMap(days);
    const frag = document.createDocumentFragment();

    const laneCache = new Map();
    for (const day of days) {
        for (const scope of day.scopes) {
            const capacityPeak = maxCapacity(scope.intervals, allTimes);
            const eventPeak = maxConcurrentEvents(scope.events || []);
            const lanes = Math.max(1, capacityPeak, eventPeak);
            laneCache.set(`${day.date}_${scope.id}`, lanes);
        }
    }
    const getLanes = (dateIso, scopeId) => laneCache.get(`${dateIso}_${scopeId}`) || 1;

    const eventsIndex = new Map();
    for (const day of days) {
        for (const scope of day.scopes) {
            const key = `${day.date}_${scope.id}`;
            eventsIndex.set(key, indexEventsByStart(scope.events || []));
        }
    }
    const templateCols = ['max-content'];
    days.forEach((day, dayIdx) => {
        day.scopes.forEach((scope, scopeIdx) => {
            const lanes = getLanes(day.date, scope.id);
            templateCols.push(`repeat(${lanes}, minmax(120px,1fr))`);
            if (scopeIdx < day.scopes.length - 1) templateCols.push('2px');
        });
        if (dayIdx < days.length - 1) templateCols.push('4px');
    });
    container.style.display = 'grid';
    container.style.gridTemplateColumns = templateCols.join(' ');
    container.style.minWidth = 'fit-content';

    const addCell = ({text = '', className = '', row, col, rowSpan = 1, colSpan = 1, id = null, dataStatus = null}) => {
        const div = document.createElement('div');
        if (text) div.textContent = text;
        div.className = className;
        div.style.gridRow = `${row} / span ${rowSpan}`;
        div.style.gridColumn = `${col} / span ${colSpan}`;
        if (id) div.id = id;
        if (dataStatus) div.dataset.status = dataStatus;
        frag.appendChild(div);
        return div;
    };

    addCell({
        text: 'Datum',
        className: 'overall-calendar-head overall-calendar-day-header overall-calendar-sticky-corner',
        row: 1, col: 1
    });

    let colCursor = 2, totalRows = allTimes.length + 2;
    days.forEach((day, dayIdx) => {
        const daySpan = day.scopes.reduce((totalCols, scope, scopeIndex) => {
            const laneCount = getLanes(day.date, scope.id);
            const hasNextScope = scopeIndex < day.scopes.length - 1;
            return totalCols + laneCount + (hasNextScope ? 1 : 0);
        }, 0);
        const label = new Date(day.date).toLocaleDateString('de-DE', {
            weekday: 'short',
            day: '2-digit',
            month: '2-digit'
        });

        addCell({
            text: label,
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
        const dateIso = day.date;
        day.scopes.forEach((scope, scopeIdx) => {
            const meta = calendarMeta.scopes?.[scope.id] || {};
            const lanes = getLanes(day.date, scope.id);
            const head = addCell({
                text: meta.shortName || meta.name || `Scope ${scope.id}`,
                className: 'overall-calendar-head overall-calendar-scope-header overall-calendar-stick-top',
                row: 2, col: colCursor, colSpan: lanes
            });
            head.style.background = SCOPE_COLORS[scope.id];
            if (isScopeClosed(dateIso, scope.id)) head.classList.add('is-closed');

            colCursor += lanes;
            if (scopeIdx < day.scopes.length - 1) {
                addCell({className: 'overall-calendar-separator', row: 2, col: colCursor, rowSpan: totalRows - 1});
                colCursor++;
            }
        });
        if (dayIdx < days.length - 1) {
            addCell({className: 'overall-calendar-day-separator', row: 1, col: colCursor, rowSpan: totalRows});
            colCursor++;
        }
    });

    const occupied = new Set();
    const toKey = (r, c) => `${r}-${c}`;

    allTimes.forEach((time, tIdx) => {
        const row = tIdx + 3;

        addCell({
            text: time,
            className: 'overall-calendar-time overall-calendar-stick-left',
            row, col: 1
        });

        let col = 2;
        days.forEach((day, dayIdx) => {
            const dateIso = day.date;

            day.scopes.forEach((scope, scopeIdx) => {
                const lanes = getLanes(day.date, scope.id);
                const idxKey = `${day.date}_${scope.id}`;
                const byStart = eventsIndex.get(idxKey) || new Map();
                const startingNow = byStart.get(time) || [];

                for (const ev of startingNow) {
                    const spanMin = Math.max(1, timeToMin(ev.end) - timeToMin(ev.start));
                    const spanRows = Math.max(1, Math.ceil(spanMin / STEP_MIN));

                    for (let ln = 0; ln < lanes; ln++) {
                        const laneCol = col + ln;
                        if (occupied.has(toKey(row, laneCol))) continue;

                        const cell = addCell({
                            text: ev.processId ?? '',
                            className: 'overall-calendar-seat overall-calendar-termin',
                            row, col: laneCol, rowSpan: spanRows,
                            dataStatus: ev.status
                        });
                        cell.style.background = SCOPE_COLORS[scope.id];
                        if (ev.status === 'cancelled') cell.classList.add('overall-calendar-cancelled');

                        for (let i = 0; i < spanRows; i++) occupied.add(toKey(row + i, laneCol));
                        break;
                    }
                }

                const capNow = capacityAt(scope.intervals, time);
                const closed = isScopeClosed(dateIso, scope.id);
                const maxOpenLanes = Math.min(lanes, capNow);
                // for (let ln = 0; ln < lanes; ln++) {
                // somit werden keine Leere Zellen erstellt (empty)
                for (let ln = 0; ln < maxOpenLanes; ln++) {
                    const laneCol = col + ln;
                    if (occupied.has(toKey(row, laneCol))) continue;

                    const isOpenLane = ln < capNow;
                    addCell({
                        className: `overall-calendar-seat overall-calendar-${isOpenLane ? 'open' : 'empty'}${closed ? ' overall-calendar-closed' : ''}`,
                        row, col: laneCol
                    });
                }

                col += lanes;
                if (scopeIdx < day.scopes.length - 1) col++;
            });

            if (dayIdx < days.length - 1) col++;
        });
    });

    const parent = container.parentNode;
    const next = container.cloneNode(false);
    const oldId = container.id;

    next.removeAttribute('id');
    next.style.display = 'grid';
    next.style.gridTemplateColumns = templateCols.join(' ');
    next.style.minWidth = 'fit-content';
    next.appendChild(frag);

    parent.insertBefore(next, container);

    container.id = oldId + '__old__' + Date.now();
    next.id = oldId;

    container.style.display = 'none';
    deferredRemove(container);

    const fsBtn = document.getElementById('calendar-fullscreen');
    if (fsBtn && next.children.length) fsBtn.style.display = 'inline-block';
}

function togglePageScroll(disable) {
    document.documentElement.classList.toggle('no-page-scroll', disable);
    document.body.classList.toggle('no-page-scroll', disable);
}

function isScopeClosed(dateIso, scopeId) {
    return CLOSURES.has(`${dateIso}|${scopeId}`);
}

function buildTimeAxis(axis) {
    const toHHMM = m => String(Math.floor(m / 60)).padStart(2, '0') + ':' + String(m % 60).padStart(2, '0');
    const start = timeToMin(axis.start);
    const end = timeToMin(axis.end);

    const output = [];
    for (let t = start; t < end; t += STEP_MIN) output.push(toHHMM(t));
    return output;
}

function capacityAt(intervals, hhmm) {
    if (!Array.isArray(intervals)) return 0;
    let sum = 0;
    for (const interval of intervals) {
        const capacity = Number.isFinite(interval?.capacity) ? interval.capacity : 0;
        if (hhmm >= interval.start && hhmm < interval.end) sum += capacity;
    }
    return sum;
}

function maxCapacity(intervals, allTimes) {
    let max = 1;
    for (const time of allTimes) {
        max = Math.max(max, capacityAt(intervals, time));
    }
    return max;
}

function maxConcurrentEvents(events = []) {
    const timeline = [];

    for (const event of events) {
        const startMin = timeToMin(event.start);
        const endMin = timeToMin(event.end);

        if (Number.isFinite(startMin) && Number.isFinite(endMin) && endMin > startMin) {
            timeline.push([startMin, +1]);
            timeline.push([endMin, -1]);
        }
    }

    timeline.sort((a, b) => a[0] - b[0] || a[1] - b[1]);

    let activeCount = 0;
    let maxConcurrent = 0;

    for (const [, change] of timeline) {
        activeCount += change;
        if (activeCount > maxConcurrent) {
            maxConcurrent = activeCount;
        }
    }

    return Math.max(1, maxConcurrent);
}

function indexEventsByStart(events = []) {
    const map = new Map();
    for (const ev of events) {
        const key = ev.start;
        if (!map.has(key)) map.set(key, []);
        map.get(key).push(ev);
    }
    return map;
}

function timeToMin(hhmm) {
    return parseInt(hhmm.slice(0, 2), 10) * 60 + parseInt(hhmm.slice(3, 5), 10);
}

function deferredRemove(node) {
    const remove = () => { node.remove(); };
    if (typeof window.requestIdleCallback === 'function') {
        window.requestIdleCallback(remove);
    } else {
        setTimeout(remove, 0);
    }
}


