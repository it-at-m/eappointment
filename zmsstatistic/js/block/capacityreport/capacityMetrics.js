import { getCapacityTableRowValue } from './exchangeData';

export function getCapacityMetricSpecs(chartValueMode, supportsMinutes) {
    const minutes = chartValueMode === 'minutes' && supportsMinutes;
    if (minutes) {
        return {
            booked: {
                totalVariable: 'bookedminutes',
                totalPosition: 4,
                publicVariable: 'bookedminutes_public',
                publicPosition: 8,
            },
            planned: {
                totalVariable: 'plannedminutes',
                totalPosition: 5,
                publicVariable: 'plannedminutes_public',
                publicPosition: 9,
            },
        };
    }

    return {
        booked: {
            totalVariable: 'bookedcount',
            totalPosition: 2,
            publicVariable: 'bookedcount_public',
            publicPosition: 6,
        },
        planned: {
            totalVariable: 'plannedcount',
            totalPosition: 3,
            publicVariable: 'plannedcount_public',
            publicPosition: 7,
        },
    };
}

export function getCapacityChannelLabel(chartChannelMode) {
    if (chartChannelMode === 'public') {
        return 'Internet';
    }
    if (chartChannelMode === 'intern_only') {
        return 'nur intern';
    }

    return 'insgesamt';
}

export function getChannelCapacityMetric(row, metric, chartChannelMode, chartValueMode, supportsMinutes) {
    const specs = getCapacityMetricSpecs(chartValueMode, supportsMinutes)[metric];
    const total = Number(getCapacityTableRowValue(
        row,
        specs.totalVariable,
        specs.totalPosition
    )) || 0;
    const bookedPublic = Number(getCapacityTableRowValue(
        row,
        specs.publicVariable,
        specs.publicPosition
    )) || 0;

    if (chartChannelMode === 'public') {
        return bookedPublic;
    }
    if (chartChannelMode === 'intern_only') {
        return Math.max(0, total - bookedPublic);
    }

    return total;
}

export function getCapacityTableHeaderLabel(kind, chartChannelMode, chartValueMode, supportsMinutes) {
    const showMinutes = chartValueMode === 'minutes' && supportsMinutes;
    const unit = showMinutes ? 'Minuten' : 'Zeitschlitze';
    const prefix = kind === 'planned' ? 'Geplante' : 'Gebuchte';

    return `${prefix} Kapazität ${getCapacityChannelLabel(chartChannelMode)} (${unit})`;
}

export function supportsMinutesChartMode(data) {
    const visualization = data && data.visualization;
    return Boolean(
        visualization
        && Array.isArray(visualization.ylabelMinutes)
        && visualization.ylabelMinutes.length > 0
    );
}

export function supportsCapacityChannelMode({
    data,
    tableDataSparse,
    tableDataFull,
}) {
    const visualization = data && data.visualization;
    if (visualization && visualization.allowCapacityChannel) {
        return true;
    }

    const rows = tableDataSparse || tableDataFull || (data && data.data);
    if (!Array.isArray(rows) || rows.length === 0) {
        return false;
    }

    const firstRow = rows[0];
    if (Array.isArray(firstRow)) {
        return firstRow.length > 6;
    }

    return firstRow.bookedcount_public !== undefined;
}

export function getActiveYLabels(data, chartChannelMode, chartValueMode, supportsMinutes) {
    const visualization = data.visualization;
    if (chartChannelMode === 'public') {
        if (chartValueMode === 'minutes' && supportsMinutes) {
            return visualization.ylabelMinutesPublic || visualization.ylabelMinutes;
        }
        return visualization.ylabelPublic || visualization.ylabel;
    }
    if (chartValueMode === 'minutes' && supportsMinutes) {
        return visualization.ylabelMinutes;
    }
    return visualization.ylabel;
}

export function getChartDatasetYLabels(activeYLabels) {
    return Array.isArray(activeYLabels) ? [...activeYLabels].reverse() : activeYLabels;
}

export function getYAxisTitle(chartValueMode, supportsMinutes) {
    if (chartValueMode === 'minutes' && supportsMinutes) {
        return 'Minuten';
    }
    return 'Zeitschlitze';
}
