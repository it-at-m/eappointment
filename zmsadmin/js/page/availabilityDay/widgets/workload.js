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
                display: false, // This will hide the grid lines for the x-axis
            },
            barPercentage: 1,
            categoryPercentage: 1
        },
        y: {
            stacked: true,
            beginAtZero: true,
            grid: {
                display: false, // This will hide the grid lines for the y-axis
            },

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



// Adjusted generateTestData for stacked bar chart
const generateTestData = () => {
    const data = {
        labels: [],
        datasets: [
            {
                label: 'Occupied Slots',
                data: [],
                backgroundColor: 'rgba(255, 99, 132, 0.5)',
                barThickness: 'flex',
            },
            {
                label: 'Free Slots',
                data: [],
                backgroundColor: 'rgba(75, 192, 192, 0.5)',
                barThickness: 'flex',
            }
        ]
    };

    let currentTime = new Date('2024-01-01T08:00:00');
    const addMinutes = (date, minutes) => new Date(date.getTime() + minutes * 60000);

    for (let i = 0; i < 120; i++) { // For simplicity, using 12 data points (1 hour)
        const timeLabel = `${currentTime.getHours()}:${currentTime.getMinutes() < 10 ? '0' : ''}${currentTime.getMinutes()}`;
        data.labels.push(timeLabel);

        const totalSlots = Math.floor(Math.random() * 50) + 50; // Random total slots between 50 and 99
        const occupiedSlots = Math.floor(Math.random() * totalSlots); // Random occupied slots within total
        const freeSlots = totalSlots - occupiedSlots; // Calculate remaining free slots

        data.datasets[0].data.push(occupiedSlots);
        data.datasets[1].data.push(freeSlots);

        currentTime = addMinutes(currentTime, 5); // Increase by 5 minutes
    }

    return data;
};

export const Workload = ({ slotBuckets }) => {
    const data = generateTestData();

    const slotBucketData = transformSlotBucketsToChartData(slotBuckets);

    return <Bar options={options} data={slotBucketData} />;
};

