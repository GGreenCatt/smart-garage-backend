// admin_reports_data.js - Mock Data for Charts

export const REVENUE_DATA = {
    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
    datasets: [{
        label: 'Revenue ($)',
        data: [1200, 1950, 1500, 2800, 2100, 3200, 1800],
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        tension: 0.4,
        fill: true
    }]
};

export const REPAIR_STATS = {
    labels: ['Pending', 'In Progress', 'Waiting Parts', 'Completed'],
    datasets: [{
        label: 'Repair Status',
        data: [5, 12, 3, 28],
        backgroundColor: [
            '#e2e8f0', // Pending - Slate 200
            '#3b82f6', // In Progress - Blue 500
            '#f59e0b', // Waiting - Amber 500
            '#22c55e'  // Completed - Green 500
        ],
        borderWidth: 0
    }]
};

export const TOP_PARTS = {
    labels: ['Brake Pads', 'Oil Filter', 'Spark Plugs', 'Batteries', 'Tires'],
    datasets: [{
        label: 'Units Sold',
        data: [45, 80, 60, 25, 30],
        backgroundColor: '#6366f1',
        borderRadius: 4
    }]
};

export const EFFICIENCY_METRICS = {
    avgRepairTime: '4.2 hrs',
    customerSatisfaction: '4.8/5',
    partsUsageRate: '92%',
    technicianUtilization: '85%'
};
