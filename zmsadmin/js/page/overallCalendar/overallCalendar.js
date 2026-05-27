let calendarCache = [];
let calendarMeta = null;
let lastUpdateAfter = null;
let autoRefreshTimer = null;
let currentRequest = null;
let SCOPE_COLORS = {};
let CLOSURES = new Set();
const STEP_MIN = 5;
const MAX_DAYS = 14;

function inclusiveDayCount(dateFrom, dateUntil) {
    const from = new Date(dateFrom);
    const until = new Date(dateUntil);
    const millisecondsPerDay = 24 * 60 * 60 * 1000;
    return Math.round((until - from) / millisecondsPerDay) + 1;
}

function eventCellLabel(event) {
    const displayNumber = event?.displayNumber;
    if (displayNumber != null && String(displayNumber).trim() !== '') {
        return String(displayNumber);
    }
    return event?.processId != null ? String(event.processId) : '';
}

function buildScopeColorMap(days) {
    const ids = [...new Set(days.flatMap(day => day.scopes.map(scope => scope.id)))];
    const map = Object.create(null);
    ids.forEach((id, index) => {
        const hue = Math.round((index * 137.508) % 360);
        map[id] = `hsl(${hue} 60% 85%)`;
    });
    return map;
}

function toMysql(date) {
    const parsedDate = (date instanceof Date) ? date : new Date(date);
    const year = parsedDate.getFullYear();
    const month = String(parsedDate.getMonth() + 1).padStart(2, '0');
    const day = String(parsedDate.getDate()).padStart(2, '0');
    const hours = String(parsedDate.getHours()).padStart(2, '0');
    const minutes = String(parsedDate.getMinutes()).padStart(2, '0');
    const seconds = String(parsedDate.getSeconds()).padStart(2, '0');
    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}

async function fetchClosures({scopeIds, dateFrom, dateUntil}) {
    const response = await fetch(`closureData/?${new URLSearchParams({
        scopeIds: scopeIds.join(','),
        dateFrom,
        dateUntil
    })}`);
    if (!response.ok) throw new Error('Fehler beim Laden der Closures');
    const {data} = await response.json();
    const set = new Set();
    for (const closureItem of (data?.items || [])) {
        set.add(`${closureItem.date}|${closureItem.scopeId}`);
    }
    CLOSURES = set;
}

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('overall-calendar-form');
    const refreshButton = document.getElementById('refresh-calendar');
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

    if (refreshButton) {
        refreshButton.addEventListener('click', async () => {
            if (!currentRequest) return;
            const {scopeIds, dateFrom, dateUntil} = currentRequest;
            try {
                await Promise.all([
                    fetchCalendar({scopeIds, dateFrom, dateUntil, fullReload: false}),
                    fetchClosures({scopeIds, dateFrom, dateUntil})
                ]);
                renderMultiDayCalendar(calendarCache);
            } catch (error) {
                alert('Fehler beim Aktualisieren: ' + error.message);
            }
        });
    }

    const fullscreenButton = document.getElementById('calendar-fullscreen');
    const wrapper = document.querySelector('.overall-calendar-wrapper');
    if (fullscreenButton && wrapper) {
        fullscreenButton.addEventListener('click', () => {
            const isFullscreen = wrapper.classList.toggle('fullscreen');
            fullscreenButton.classList.toggle('is-active', isFullscreen);
            fullscreenButton.title = isFullscreen ? 'Vollbild schließen' : 'Vollbild';
            togglePageScroll(isFullscreen);
            if (isFullscreen) fullscreenButton.focus();
        });
        document.addEventListener('keydown', keyboardEvent => {
            if (keyboardEvent.key === 'Escape' && wrapper.classList.contains('fullscreen')) {
                wrapper.classList.remove('fullscreen');
                fullscreenButton.classList.remove('is-active');
                fullscreenButton.title = 'Vollbild';
                togglePageScroll(false);
                fullscreenButton.focus();
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
        maxDate.setDate(maxDate.getDate() + (MAX_DAYS - 1));
        untilInput.min = fromInput.value;
        untilInput.max = maxDate.toISOString().slice(0, 10);
        if (untilInput.value < untilInput.min || untilInput.value > untilInput.max) {
            untilInput.value = untilInput.min;
        }
    }
});

async function fetchCalendar({scopeIds, dateFrom, dateUntil, fullReload = false}) {
    const requestParams = {scopeIds: scopeIds.join(','), dateFrom, dateUntil};
    if (!fullReload && lastUpdateAfter) {
        requestParams.updateAfter = lastUpdateAfter;
    }

    const response = await fetch(`overallcalendarData/?${new URLSearchParams(requestParams)}`);
    if (!response.ok) throw new Error('Fehler beim Laden des Kalenders');
    const json = await response.json();
    calendarMeta = json.data.meta || calendarMeta;
    const serverTimestamp = json.data.maxUpdatedAt || toMysql(new Date());

    if (fullReload) {
        calendarCache = json.data.days;
        sortCalendarCache();
    } else {
        mergeDelta(json.data.days, json.data.deletedProcessIds || []);
    }

    currentRequest = {scopeIds, dateFrom, dateUntil};
    lastUpdateAfter = serverTimestamp;
}

async function handleSubmit(event) {
    event.preventDefault();

    const select = event.target.querySelector('select[name="scopes[]"]');
    const scopeIds = Array.from(select.selectedOptions).map(option => option.value);
    const dateFrom = event.target.querySelector('input[name="calendarDateFrom"]').value;
    const dateUntil = event.target.querySelector('input[name="calendarDateUntil"]').value;
    const errorBox = document.getElementById('scope-error');
    const errorMessage = errorBox.querySelector('.msg');

    if (!scopeIds.length) {
        errorMessage.textContent = 'Bitte mindestens einen Standort auswählen';
        errorBox.style.display = 'inline-flex';
        return;
    }
    errorBox.style.display = 'none';
    errorMessage.textContent = '';

    if (dateFrom && dateUntil) {
        const dayCount = inclusiveDayCount(dateFrom, dateUntil);
        if (dayCount > MAX_DAYS) {
            alert('Bitte wählen Sie maximal 14 Tage aus.');
            return;
        }
    }

    try {
        await Promise.all([
            fetchCalendar({scopeIds, dateFrom, dateUntil, fullReload: true}),
            fetchClosures({scopeIds, dateFrom, dateUntil})
        ]);
        SCOPE_COLORS = buildScopeColorMap(calendarCache);
        renderMultiDayCalendar(calendarCache);
        startAutoRefresh();
    } catch (error) {
        alert('Fehler beim Laden' + error.message);
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

    const response = await fetch(`overallcalendarData/?${new URLSearchParams({
        scopeIds: scopeIds.join(','), dateFrom, dateUntil, updateAfter: lastUpdateAfter
    })}`);
    if (response.ok) {
        const json = await response.json();
        calendarMeta = json.data.meta || calendarMeta;
        lastUpdateAfter = json.data.maxUpdatedAt || toMysql(new Date());
        mergeDelta(json.data.days, json.data.deletedProcessIds || []);
    }

    await fetchClosures({scopeIds, dateFrom, dateUntil});
    renderMultiDayCalendar(calendarCache);
}

function mergeDelta(deltaDays, deletedProcessIds = []) {
    if (Array.isArray(deletedProcessIds) && deletedProcessIds.length) {
        const deletedIds = new Set(deletedProcessIds.map(Number));
        for (const day of calendarCache) {
            for (const scope of day.scopes) {
                if (!Array.isArray(scope.events)) continue;
                scope.events = scope.events.filter(event => !deletedIds.has(event.processId));
            }
        }
    }
    if (!deltaDays?.length) {
        sortCalendarCache();
        return;
    }

    for (const deltaDay of deltaDays) {
        let fullDay = calendarCache.find(cachedDay => cachedDay.date === deltaDay.date);
        if (!fullDay) {
            fullDay = {date: deltaDay.date, scopes: []};
            calendarCache.push(fullDay);
        }

        for (const deltaScope of (deltaDay.scopes || [])) {
            let fullScope = fullDay.scopes.find(scope => scope.id === deltaScope.id);
            if (!fullScope) {
                fullScope = {id: deltaScope.id, intervals: [], events: []};
                fullDay.scopes.push(fullScope);
            }
            if (Array.isArray(deltaScope.intervals)) {
                fullScope.intervals = deltaScope.intervals.map(interval => ({...interval}));
            }
        }
    }

    const latestByProcessId = new Map();
    for (const deltaDay of deltaDays) {
        for (const deltaScope of (deltaDay.scopes || [])) {
            for (const event of (deltaScope.events || [])) {
                if (!event || typeof event !== 'object') continue;
                const processId = event.processId;
                const candidate = {...event, __day: deltaDay.date, __scope: deltaScope.id};
                const previous = latestByProcessId.get(processId);
                if (!previous || (event.updatedAt && event.updatedAt >= previous.updatedAt)) {
                    latestByProcessId.set(processId, candidate);
                }
            }
        }
    }

    if (!latestByProcessId.size) {
        sortCalendarCache();
        return;
    }

    const affectedProcessIds = new Set(latestByProcessId.keys());
    for (const day of calendarCache) {
        for (const scope of day.scopes) {
            if (!Array.isArray(scope.events)) continue;
            scope.events = scope.events.filter(event => !affectedProcessIds.has(event.processId));
        }
    }

    for (const event of latestByProcessId.values()) {
        if (event.status !== 'confirmed') continue;

        let day = calendarCache.find(cachedDay => cachedDay.date === event.__day);
        if (!day) {
            day = {date: event.__day, scopes: []};
            calendarCache.push(day);
        }

        let scope = day.scopes.find(cachedScope => cachedScope.id === event.__scope);
        if (!scope) {
            scope = {id: event.__scope, intervals: [], events: []};
            day.scopes.push(scope);
        }

        if (!Array.isArray(scope.events)) scope.events = [];
        scope.events.push({
            processId: event.processId,
            displayNumber: event.displayNumber ?? null,
            start: event.start,
            end: event.end,
            status: 'confirmed',
            updatedAt: event.updatedAt
        });
    }

    sortCalendarCache();
}

function sortCalendarCache() {
    calendarCache.sort((dayA, dayB) => String(dayA.date).localeCompare(String(dayB.date)));

    for (const day of calendarCache) {
        day.scopes.sort((scopeA, scopeB) => (scopeA.id ?? 0) - (scopeB.id ?? 0));
        for (const scope of day.scopes) {
            if (!Array.isArray(scope.events)) continue;
            scope.events.sort(
                (eventA, eventB) =>
                    String(eventA.start).localeCompare(String(eventB.start)) ||
                    String(eventA.updatedAt || '').localeCompare(String(eventB.updatedAt || '')) |
                    (eventA.processId - eventB.processId)
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
        const emptyMessage = document.createElement('p');
        emptyMessage.textContent = 'Keine Daten verfügbar.';
        next.appendChild(emptyMessage);

        parent.insertBefore(next, container);

        container.id = oldId + '__old__' + Date.now();
        next.id = oldId;

        container.style.display = 'none';
        deferredRemove(container);

        const fullscreenButton = document.getElementById('calendar-fullscreen');
        if (fullscreenButton) {
            fullscreenButton.style.display = 'none';
            exitCalendarFullscreen();
        }
        return;
    }


    const axis = calendarMeta.axis;
    const allTimes = buildTimeAxis(axis);
    const fragment = document.createDocumentFragment();

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
    days.forEach((day, dayIndex) => {
        day.scopes.forEach((scope, scopeIndex) => {
            const lanes = getLanes(day.date, scope.id);
            templateCols.push(`repeat(${lanes}, minmax(120px,1fr))`);
            if (scopeIndex < day.scopes.length - 1) templateCols.push('2px');
        });
        if (dayIndex < days.length - 1) templateCols.push('4px');
    });
    container.style.display = 'grid';
    container.style.gridTemplateColumns = templateCols.join(' ');
    container.style.minWidth = 'fit-content';

    const addCell = ({text = '', className = '', row, col, rowSpan = 1, colSpan = 1, id = null, dataStatus = null}) => {
        const cellElement = document.createElement('div');
        if (text) cellElement.textContent = text;
        cellElement.className = className;
        cellElement.style.gridRow = `${row} / span ${rowSpan}`;
        cellElement.style.gridColumn = `${col} / span ${colSpan}`;
        if (id) cellElement.id = id;
        if (dataStatus) cellElement.dataset.status = dataStatus;
        fragment.appendChild(cellElement);
        return cellElement;
    };

    addCell({
        text: 'Datum',
        className: 'overall-calendar-head overall-calendar-day-header overall-calendar-sticky-corner',
        row: 1, col: 1
    });

    let columnCursor = 2, totalRows = allTimes.length + 2;
    days.forEach((day, dayIndex) => {
        const daySpan = day.scopes.reduce((totalColumns, scope, scopeIndex) => {
            const laneCount = getLanes(day.date, scope.id);
            const hasNextScope = scopeIndex < day.scopes.length - 1;
            return totalColumns + laneCount + (hasNextScope ? 1 : 0);
        }, 0);
        const label = new Date(day.date).toLocaleDateString('de-DE', {
            weekday: 'short',
            day: '2-digit',
            month: '2-digit'
        });

        addCell({
            text: label,
            className: 'overall-calendar-head overall-calendar-day-header overall-calendar-stick-top',
            row: 1, col: columnCursor, colSpan: daySpan
        });

        columnCursor += daySpan;
        if (dayIndex < days.length - 1) columnCursor += 1;
    });

    addCell({
        text: 'Zeit',
        className: 'overall-calendar-head overall-calendar-scope-header overall-calendar-stick-left',
        row: 2, col: 1
    });

    columnCursor = 2;
    days.forEach((day, dayIndex) => {
        const dateIso = day.date;
        day.scopes.forEach((scope, scopeIndex) => {
            const meta = calendarMeta.scopes?.[scope.id] || {};
            const lanes = getLanes(day.date, scope.id);
            const headerCell = addCell({
                text: meta.shortName || meta.name || `Scope ${scope.id}`,
                className: 'overall-calendar-head overall-calendar-scope-header overall-calendar-stick-top',
                row: 2, col: columnCursor, colSpan: lanes
            });
            headerCell.style.background = SCOPE_COLORS[scope.id];
            if (isScopeClosed(dateIso, scope.id)) headerCell.classList.add('is-closed');

            columnCursor += lanes;
            if (scopeIndex < day.scopes.length - 1) {
                addCell({className: 'overall-calendar-separator', row: 2, col: columnCursor, rowSpan: totalRows - 1});
                columnCursor++;
            }
        });
        if (dayIndex < days.length - 1) {
            addCell({className: 'overall-calendar-day-separator', row: 1, col: columnCursor, rowSpan: totalRows});
            columnCursor++;
        }
    });

    const occupied = new Set();
    const rowColumnKey = (row, column) => `${row}-${column}`;

    allTimes.forEach((time, timeIndex) => {
        const row = timeIndex + 3;

        addCell({
            text: time,
            className: 'overall-calendar-time overall-calendar-stick-left',
            row, col: 1
        });

        let column = 2;
        days.forEach((day, dayIndex) => {
            const dateIso = day.date;

            day.scopes.forEach((scope, scopeIndex) => {
                const lanes = getLanes(day.date, scope.id);
                const eventsIndexKey = `${day.date}_${scope.id}`;
                const eventsByStart = eventsIndex.get(eventsIndexKey) || new Map();
                const startingNow = eventsByStart.get(time) || [];

                for (const event of startingNow) {
                    const spanMinutes = Math.max(1, timeToMin(event.end) - timeToMin(event.start));
                    const spanRows = Math.max(1, Math.ceil(spanMinutes / STEP_MIN));

                    for (let laneIndex = 0; laneIndex < lanes; laneIndex++) {
                        const laneColumn = column + laneIndex;
                        if (occupied.has(rowColumnKey(row, laneColumn))) continue;

                        const cell = addCell({
                            text: eventCellLabel(event),
                            className: 'overall-calendar-seat overall-calendar-termin',
                            row, col: laneColumn, rowSpan: spanRows,
                            dataStatus: event.status
                        });
                        cell.style.background = SCOPE_COLORS[scope.id];
                        if (event.status === 'cancelled') cell.classList.add('overall-calendar-cancelled');

                        for (let spanRowIndex = 0; spanRowIndex < spanRows; spanRowIndex++) {
                            occupied.add(rowColumnKey(row + spanRowIndex, laneColumn));
                        }
                        break;
                    }
                }

                const capacityNow = capacityAt(scope.intervals, time);
                const closed = isScopeClosed(dateIso, scope.id);
                const maxOpenLanes = Math.min(lanes, capacityNow);
                // for (let laneIndex = 0; laneIndex < lanes; laneIndex++) {
                // somit werden keine Leere Zellen erstellt (empty)
                for (let laneIndex = 0; laneIndex < maxOpenLanes; laneIndex++) {
                    const laneColumn = column + laneIndex;
                    if (occupied.has(rowColumnKey(row, laneColumn))) continue;

                    const isOpenLane = laneIndex < capacityNow;
                    addCell({
                        className: `overall-calendar-seat overall-calendar-${isOpenLane ? 'open' : 'empty'}${closed ? ' overall-calendar-closed' : ''}`,
                        row, col: laneColumn
                    });
                }

                column += lanes;
                if (scopeIndex < day.scopes.length - 1) column++;
            });

            if (dayIndex < days.length - 1) column++;
        });
    });

    const parent = container.parentNode;
    const next = container.cloneNode(false);
    const oldId = container.id;

    next.removeAttribute('id');
    next.style.display = 'grid';
    next.style.gridTemplateColumns = templateCols.join(' ');
    next.style.minWidth = 'fit-content';
    next.appendChild(fragment);

    parent.insertBefore(next, container);

    container.id = oldId + '__old__' + Date.now();
    next.id = oldId;

    container.style.display = 'none';
    deferredRemove(container);

    const fullscreenButton = document.getElementById('calendar-fullscreen');
    if (fullscreenButton) {
        if (next.children.length) {
            fullscreenButton.style.display = 'inline-block';
        } else {
            fullscreenButton.style.display = 'none';
            exitCalendarFullscreen();
        }
    }
}

function exitCalendarFullscreen() {
    const wrapper = document.querySelector('.overall-calendar-wrapper');
    const fullscreenButton = document.getElementById('calendar-fullscreen');
    if (!wrapper?.classList.contains('fullscreen')) return;
    wrapper.classList.remove('fullscreen');
    if (fullscreenButton) {
        fullscreenButton.classList.remove('is-active');
        fullscreenButton.title = 'Vollbild';
    }
    togglePageScroll(false);
}

function togglePageScroll(disable) {
    document.documentElement.classList.toggle('no-page-scroll', disable);
    document.body.classList.toggle('no-page-scroll', disable);
}

function isScopeClosed(dateIso, scopeId) {
    return CLOSURES.has(`${dateIso}|${scopeId}`);
}

function buildTimeAxis(axis) {
    const minutesToHhmm = minutesFromMidnight =>
        String(Math.floor(minutesFromMidnight / 60)).padStart(2, '0') + ':' + String(minutesFromMidnight % 60).padStart(2, '0');
    const start = timeToMin(axis.start);
    const end = timeToMin(axis.end);

    const output = [];
    for (let minuteOffset = start; minuteOffset < end; minuteOffset += STEP_MIN) {
        output.push(minutesToHhmm(minuteOffset));
    }
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

    timeline.sort((entryA, entryB) => entryA[0] - entryB[0] || entryA[1] - entryB[1]);

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
    for (const event of events) {
        const key = event.start;
        if (!map.has(key)) map.set(key, []);
        map.get(key).push(event);
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