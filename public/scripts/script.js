const tasks = [
    {
        id: 1,
        title: "Main Task 1",
        subTasks: [
            {
                id: 11,
                title: "Sub Task 1.1",
                subTasks: []
            },
            {
                id: 12,
                title: "Sub Task 1.2",
                subTasks: [
                    {
                        id: 121,
                        title: "Sub Sub Task 1.2.1",
                        subTasks: []
                    }
                ]
            }
        ]
    },
    {
        id: 2,
        title: "Main Task 2",
        subTasks: []
    }
];

function renderTask(task) {
    const taskElement = $(`
        <li class="list-group-item">
            <button class="btn btn-link accordion-button" type="button" data-toggle="collapse" data-target="#task-${task.id}">
                &#9658;
            </button>
            ${task.title}
            <div id="task-${task.id}" class="collapse ml-3">
                <!-- Subtasks will be dynamically added here -->
            </div>
        </li>
    `);

    if (task.subTasks.length > 0) {
        const subTaskList = taskElement.find(`#task-${task.id}`);
        task.subTasks.forEach(subTask => {
            const subTaskElement = renderTask(subTask);
            subTaskList.append(subTaskElement);
        });
    }

    return taskElement;
}

$(document).ready(() => {
    const tasksList = $("#tasks");
    tasks.forEach(task => {
        const taskElement = renderTask(task);
        tasksList.append(taskElement);
    });
});