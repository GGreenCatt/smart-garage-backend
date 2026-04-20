
// Mock Data
let tasks = [
    { id: 'T-101', vehicle: 'Toyota Camry (2019)', defect: 'Brake Pad Replacement', status: 'todo', priority: 'high', date: 'Today', technician: 'Alex' },
    { id: 'T-102', vehicle: 'Honda CR-V (2021)', defect: 'Oil Change & Filter', status: 'inprogress', priority: 'medium', date: 'Today', technician: 'Alex' },
    { id: 'T-103', vehicle: 'Ford Ranger (2018)', defect: 'Alternator Check', status: 'todo', priority: 'low', date: 'Tomorrow', technician: 'Alex' },
    { id: 'T-104', vehicle: 'Mazda 3 (2020)', defect: 'Tire Rotation', status: 'done', priority: 'medium', date: 'Yesterday', technician: 'Alex' },
    { id: 'T-105', vehicle: 'Hyundai Tucson', defect: 'AC Recharge', status: 'todo', priority: 'medium', date: 'Dec 25', technician: 'Alex' }
];

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    renderBoard();
    updateStats();
});

// Render Board
function renderBoard() {
    ['todo', 'inprogress', 'done'].forEach(status => {
        const col = document.getElementById(`col-${status}`);
        col.innerHTML = '';

        const filteredTasks = tasks.filter(t => t.status === status);

        filteredTasks.forEach(task => {
            const card = createTaskCard(task);
            col.appendChild(card);
        });

        // Update counts
        const countBadge = document.getElementById(`count-${status}`);
        if (countBadge) countBadge.innerText = filteredTasks.length;
    });
}

// Create Card Element
function createTaskCard(task) {
    const div = document.createElement('div');
    div.className = 'task-card bg-white p-4 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition cursor-grab group relative';
    div.draggable = true;
    div.dataset.id = task.id;

    // Priority Colors
    const priorityColor = task.priority === 'high' ? 'text-red-500 bg-red-50' :
        task.priority === 'medium' ? 'text-amber-500 bg-amber-50' : 'text-blue-500 bg-blue-50';

    div.innerHTML = `
        <div class="flex justify-between items-start mb-2">
            <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">${task.id}</span>
            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded ${priorityColor}">${task.priority}</span>
        </div>
        <h4 class="font-bold text-slate-800 text-sm mb-1">${task.vehicle}</h4>
        <p class="text-xs text-slate-500 mb-3 line-clamp-2">${task.defect}</p>
        
        <div class="flex items-center justify-between border-t border-gray-50 pt-3 mt-2">
            <div class="flex items-center gap-2">
                <i class="fas fa-calendar-alt text-gray-300 text-xs"></i>
                <span class="text-xs font-medium text-gray-400">${task.date}</span>
            </div>
            <button onclick="openTaskModal('${task.id}')" class="w-6 h-6 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition">
                <i class="fas fa-chevron-right text-[10px]"></i>
            </button>
        </div>
    `;

    // Drag Events
    div.addEventListener('dragstart', (e) => {
        e.dataTransfer.setData('text/plain', task.id);
        div.classList.add('opacity-50', 'scale-95');
        document.body.classList.add('dragging-active');
    });

    div.addEventListener('dragend', () => {
        div.classList.remove('opacity-50', 'scale-95');
        document.body.classList.remove('dragging-active');
        document.querySelectorAll('.kanban-col').forEach(c => c.classList.remove('drag-over'));
    });

    return div;
}

// Drag & Drop Handlers (Global)
window.allowDrop = function (e) {
    e.preventDefault();
    const col = e.target.closest('.kanban-col');
    if (col) col.classList.add('drag-over');
};

window.leaveDrag = function (e) {
    const col = e.target.closest('.kanban-col');
    if (col) col.classList.remove('drag-over');
};

window.drop = function (e) {
    e.preventDefault();
    const col = e.target.closest('.kanban-col');
    if (!col) return;

    col.classList.remove('drag-over');
    const taskId = e.dataTransfer.getData('text/plain');
    const newStatus = col.dataset.status;

    const taskIndex = tasks.findIndex(t => t.id === taskId);
    if (taskIndex > -1 && tasks[taskIndex].status !== newStatus) {
        tasks[taskIndex].status = newStatus;
        renderBoard();
        // Here you would typically sync with server/localStorage
        // play drop sound or animation
    }
};

// Modal Logic
window.openTaskModal = function (id) {
    const task = tasks.find(t => t.id === id);
    if (!task) return;

    document.getElementById('modal-title').innerText = `Repair Task #${task.id}`;
    document.getElementById('modal-vehicle').innerText = task.vehicle;
    document.getElementById('modal-defect').innerText = task.defect;

    const modal = document.getElementById('taskModal');
    modal.classList.remove('hidden');
}

window.closeTaskModal = function () {
    document.getElementById('taskModal').classList.add('hidden');
}

function updateStats() {
    // Placeholder for future global stats (e.g. progress bar)
}
