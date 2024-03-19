import React from 'react';
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend } from 'chart.js';
import { Bar } from 'react-chartjs-2';

ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend
);

const options = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top',
        },
        title: {
            display: true,
            text: 'Terminkunden',
        },
    },
    scales: {
        x: {
            stacked: true,
            grid: {
                display: false,
            },
            barPercentage: 1,
            categoryPercentage: 1,
            ticks: {
                callback: function (val, index, ticks) {
                    // Assuming this is your previously configured x-axis callback
                    const label = this.getLabelForValue(val);
                    if (label && label.endsWith(':00')) {
                        return label;
                    } else {
                        return null;
                    }
                }
            }
        },
        y: {
            stacked: true,
            beginAtZero: true,
            grid: {
                display: false,
            },
            ticks: {
                stepSize: 1,
                callback: function (value) {
                    if (value % 1 === 0) {
                        return value;
                    }
                }
            }
        },
    },
};




function transformSlotBucketsToChartData(slotBuckets, timestamp) {
    const now = new Date();
    const dayDate = new Date(timestamp * 1000);
    const dayString = dayDate.toISOString().split('T')[0];
    const labels = Object.keys(slotBuckets);

    const datasets = {
        past: {
            label: 'Vergangene Slots',
            data: new Array(labels.length).fill(0),
            backgroundColor: 'rgba(211, 211, 211, 0.5)',
        },
        occupied: {
            label: 'Gebuchte Slots',
            data: [],
            backgroundColor: [],
        },
        available: {
            label: 'Freie Slots',
            data: [],
            backgroundColor: [],
        },
    };

    labels.forEach((label, index) => {
        const slot = slotBuckets[label];
        const totalInternSlots = parseInt(slot.intern, 10);
        const occupiedSlots = parseInt(slot.occupiedCount, 10);
        const availableSlots = Math.max(0, totalInternSlots - occupiedSlots);

        const slotDateTime = new Date(`${dayString}T${label}:00`);
        const isPast = slotDateTime < now;

        if (isPast) {
            datasets.past.data[index] = totalInternSlots;
            datasets.available.data.push(0);
            datasets.occupied.data.push(0);
        } else {
            datasets.available.data.push(availableSlots);
            datasets.occupied.data.push(occupiedSlots);
            datasets.available.backgroundColor.push('#CCE0E6');
            datasets.occupied.backgroundColor.push('rgba(255, 99, 132, 0.5)');
        }
    });

    const finalDatasets = Object.values(datasets).filter(dataset => dataset.data.some(value => value > 0));

    return {
        labels,
        datasets: finalDatasets,
    };
}




export const Workload = ({ slotBuckets, timestamp }) => {
    const slotBucketData = slotBuckets ? transformSlotBucketsToChartData(slotBuckets, timestamp) : transformSlotBucketsToChartData({}, timestamp);
    return <>
        <div style={{ height: '300px', width: '100%' }}>
            <Bar options={options} data={slotBucketData} />
        </div>
    </>
};

