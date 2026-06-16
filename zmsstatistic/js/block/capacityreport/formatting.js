export function formatCapacitySummaryNumber(value) {
    return Number(value).toLocaleString('de-DE', {
        maximumFractionDigits: 0,
    });
}

export function formatCapacitySummaryUtilization(booked, planned) {
    if (planned <= 0) {
        return '0 %';
    }

    const utilization = Math.round((booked / planned) * 1000) / 10;
    return `${utilization.toLocaleString('de-DE', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 1,
    })} %`;
}

export function formatCapacityTableDate(value, tableIsHourly) {
    if (tableIsHourly) {
        return String(value ?? '');
    }

    const datePart = String(value ?? '').substring(0, 10);
    const match = datePart.match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (!match) {
        return datePart;
    }

    return `${match[3]}.${match[2]}.${match[1]}`;
}

export function formatGermanDateForDisplay(isoDate) {
    const match = String(isoDate).match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (!match) {
        return String(isoDate);
    }

    return `${match[3]}.${match[2]}.${match[1]}`;
}

export function formatDateForFilename(isoDate) {
    const match = String(isoDate).match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if (!match) {
        return String(isoDate).replace(/[^0-9-]/g, '');
    }

    return `${match[1]}-${match[2]}-${match[3]}`;
}

export function getChartDateRangeLabel(chartDateFrom, chartDateTo, chartPeriod) {
    if (chartDateFrom && chartDateTo) {
        return `${formatGermanDateForDisplay(chartDateFrom)} bis ${formatGermanDateForDisplay(chartDateTo)}`;
    }

    if (chartPeriod) {
        return chartPeriod;
    }

    return '';
}

export function getChartDateRangeFilenamePart(chartDateFrom, chartDateTo, chartPeriod) {
    if (chartDateFrom && chartDateTo) {
        return `${formatDateForFilename(chartDateFrom)}-bis-${formatDateForFilename(chartDateTo)}`;
    }

    if (chartPeriod) {
        return String(chartPeriod).replace(/[^0-9-]/g, '');
    }

    return '';
}

export function getChartDateRangeSuffix(chartDateFrom, chartDateTo, chartPeriod) {
    const rangePart = getChartDateRangeFilenamePart(chartDateFrom, chartDateTo, chartPeriod);
    if (!rangePart) {
        return '';
    }

    return `_${rangePart}`;
}

export function getChartDownloadFilename(chartValueMode, chartDateFrom, chartDateTo, chartPeriod) {
    const suffix = chartValueMode === 'minutes' ? '-minuten' : '-zeitschlitze';
    return `terminkapazitaet${suffix}${getChartDateRangeSuffix(chartDateFrom, chartDateTo, chartPeriod)}.png`;
}
