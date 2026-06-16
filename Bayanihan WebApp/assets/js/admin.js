// Date
document.getElementById("date").innerText =
    new Date().toDateString();

// Dashboard numbers
const dashboard = {
    users: 5,
    orders: 210,
    revenue: 12800,
    tasks: 7
};

document.getElementById("users").innerText = dashboard.users;
document.getElementById("orders").innerText = dashboard.orders;
document.getElementById("revenue").innerText = "$" + dashboard.revenue;
document.getElementById("tasks").innerText = dashboard.tasks;

// User Data
let users = [
    { id: 1, name: "Admin User", role: "Admin", status: "Active" },
    { id: 2, name: "John Doe", role: "User", status: "Active" },
    { id: 3, name: "Jane Smith", role: "User", status: "Inactive" }
];

const table = document.getElementById("userTable");

function renderUsers() {
    table.innerHTML = "";
    users.forEach(user => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${user.id}</td>
            <td>${user.name}</td>
            <td>${user.role}</td>
            <td>${user.status}</td>
            <td>
                <button class="action-btn edit" onclick="editUser(${user.id})">Edit</button>
                <button class="action-btn delete" onclick="deleteUser(${user.id})">Delete</button>
            </td>
        `;
        table.appendChild(row);
    });
}

function addUser() {
    const id = users.length + 1;
    users.push({ id, name: "New User", role: "User", status: "Active" });
    dashboard.users++;
    document.getElementById("users").innerText = dashboard.users;
    renderUsers();
}

function editUser(id) {
    alert("Edit user ID: " + id);
}

function deleteUser(id) {
    users = users.filter(user => user.id !== id);
    dashboard.users--;
    document.getElementById("users").innerText = dashboard.users;
    renderUsers();
}

renderUsers();
