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
    plugins: {
        legend: {
            position: 'top',
        },
        title: {
            display: true,
            text: 'Occupied vs Total Slots Every 5 Minutes (Stacked)',
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
                callback: function(val, index, ticks) {
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
                stepSize: 1, // Ensures the step between ticks is 1
                // Optional: Round values to ensure they are integers (might not be necessary with stepSize: 1)
                callback: function(value) {
                    if (value % 1 === 0) { // Check if the value is an integer
                        return value;
                    }
                }
            }
        },
    },
};




function transformSlotBucketsToChartData(slotBuckets) {
    const labels = Object.keys(slotBuckets); // Extract time slots as labels
    const datasets = {
        occupied: { // Dataset for occupied slots
            label: 'Occupied Intern Slots',
            data: [],
            backgroundColor: 'rgba(255, 99, 132, 0.5)',
        },
        available: { // Dataset for available slots within intern
            label: 'Available Intern Slots',
            data: [],
            backgroundColor: 'rgba(75, 192, 192, 0.5)',
        },
    };

    labels.forEach(label => {
        const slot = slotBuckets[label];
        const totalInternSlots = parseInt(slot.intern, 10);
        const occupiedSlots = parseInt(slot.occupiedCount, 10);
        const availableSlots = totalInternSlots - occupiedSlots; // Calculate available slots as difference

        // For "available", push the calculated number of available slots
        datasets.available.data.push(availableSlots);
        // For "occupied", push the number of occupied slots
        datasets.occupied.data.push(occupiedSlots);
    });

    return {
        labels,
        datasets: Object.values(datasets),
    };
}




export const Workload = ({ slotBuckets }) => {
    const slotBucketData = transformSlotBucketsToChartData(slotBuckets);
    return <Bar options={options} data={slotBucketData} />;
};

