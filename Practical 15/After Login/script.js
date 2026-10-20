// Practical 10: Automatic Session Guard for all After Login pages
(function enforceSessionAuth() {
    // Only execute when loaded from an After Login page
    if (window.location.pathname.indexOf("After") !== -1 || window.location.pathname.indexOf("After%20Login") !== -1) {
        // Prevent infinite loops on login page
        fetch("auth_check.php?api=1", { credentials: "same-origin" })
            .then(function(res) {
                if (res.status === 401) {
                    window.location.href = "../Before%20Login/Login.php?error=unauthorized";
                }
                return res.json();
            })
            .then(function(data) {
                if (data && !data.authenticated) {
                    window.location.href = "../Before%20Login/Login.php?error=unauthorized";
                }
            })
            .catch(function() {
                // If offline or local file protocol, continue gracefully
            });
    }
})();

let currentDate = new Date();

function generateCalendar() {
    let monthYear = document.getElementById("month-year");
    let calendarBody = document.getElementById("calendar-body");

    if (!monthYear || !calendarBody) {
        return;
    }

    let year = currentDate.getFullYear();
    let month = currentDate.getMonth();

    let firstDay = new Date(year, month, 1).getDay();
    let lastDate = new Date(year, month + 1, 0).getDate();

    let monthNames = [
        "January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];

    monthYear.innerHTML = monthNames[month] + " " + year;
    calendarBody.innerHTML = "";

    let date = 1;

    for (let row = 0; row < 6; row++) {
        let tr = document.createElement("tr");

        for (let col = 0; col < 7; col++) {
            let td = document.createElement("td");

            if (row === 0 && col < firstDay) {
                td.innerHTML = "";
            } else if (date > lastDate) {
                td.innerHTML = "";
            } else {
                td.innerHTML = date;

                let today = new Date();

                if (
                    date === today.getDate() &&
                    month === today.getMonth() &&
                    year === today.getFullYear()
                ) {
                    td.classList.add("today");
                }

                date++;
            }

            tr.appendChild(td);
        }

        calendarBody.appendChild(tr);

        if (date > lastDate) {
            break;
        }
    }
}

function previousMonth() {
    currentDate.setMonth(currentDate.getMonth() - 1);
    generateCalendar();
}

function nextMonth() {
    currentDate.setMonth(currentDate.getMonth() + 1);
    generateCalendar();
}

generateCalendar();

let announcements = [
    "18 July - National Symposium on Innovative Pedagogy and Assessment Practices",
    "23 July - Shree Pramukh Swami Maharaj Endowment Chair - PDPIAS",
    "30 July - Cultural Fest"
];

let announcementIndex = 0;

function changeAnnouncement() {
    let announcementText = document.getElementById("announcement-text");

    if (!announcementText) {
        return;
    }

    announcementText.innerHTML = announcements[announcementIndex];

    announcementIndex++;

    if (announcementIndex >= announcements.length) {
        announcementIndex = 0;
    }
}

changeAnnouncement();
setInterval(changeAnnouncement, 8000);

let images = [
    "Images/Slider-1.jpg",
    "Images/Slider-2.jpg",
    "Images/Slider-3.jpg"
];

let index = 0;

function changeSlide(number) {
    let slide = document.getElementById("slide");

    if (!slide) {
        return;
    }

    index = number;
    slide.src = images[index];
}

setInterval(function () {
    let slide = document.getElementById("slide");

    if (!slide) {
        return;
    }

    index++;

    if (index >= images.length) {
        index = 0;
    }

    slide.src = images[index];
}, 3000);

function openModal(modalId) {
    let modal = document.getElementById(modalId);

    if (modal) {
        modal.style.display = "flex";
    }
}

function closeModal(modalId) {
    let modal = document.getElementById(modalId);

    if (modal) {
        modal.style.display = "none";
    }
}

function showNotification() {
    let notification = document.getElementById("notification");

    if (!notification) {
        return;
    }

    notification.style.display = "block";

    setTimeout(function () {
        notification.style.display = "none";
    }, 3000);
}

if (window.location.search === "?login=success") {
    showNotification();
}

function toggleFAQ(number) {
    let answer = document.getElementById("faq" + number);

    if (!answer) {
        return;
    }

    if (answer.style.display === "none" || answer.style.display === "") {
        answer.style.display = "block";
    } else {
        answer.style.display = "none";
    }
}



function toggleMenu() {
    let menu = document.getElementById("navbar");

    if (!menu) {
        return;
    }

    menu.classList.toggle("active");
}

function toggleTheme() {
    document.body.classList.toggle("dark-mode");

    let button = document.querySelector(".theme-button");

    if (document.body.classList.contains("dark-mode")) {
        localStorage.setItem("theme", "dark");

        if (button) {
            button.innerHTML = "☀️";
        }
    } else {
        localStorage.setItem("theme", "light");

        if (button) {
            button.innerHTML = "🌙";
        }
    }
}

if (localStorage.getItem("theme") === "dark") {
    document.body.classList.add("dark-mode");

    let button = document.querySelector(".theme-button");

    if (button) {
        button.innerHTML = "☀️";
    }
}


let events = [];
let currentPage = 1;
let eventsPerPage = 2;
let nonTechnicalPage = 1;

if (document.getElementById("technicalEvents") || document.getElementById("nonTechnicalEvents")) {
    fetch("events.json")
        .then(function(response) {
            if (!response.ok) {
                throw new Error("Unable to load events");
            }
            return response.json();
        })
        .then(function(data) {
            events = data.events;
            if (document.getElementById("technicalEvents")) {
                searchEvents();
            }
            if (document.getElementById("nonTechnicalEvents")) {
                searchNonTechnicalEvents();
            }
        })
        .catch(function() {
            let technicalBox = document.getElementById("technicalEvents");
            let nonTechnicalBox = document.getElementById("nonTechnicalEvents");
            if (technicalBox) technicalBox.innerHTML = "<p>Unable to load events. Please try again.</p>";
            if (nonTechnicalBox) nonTechnicalBox.innerHTML = "<p>Unable to load events. Please try again.</p>";
        });
}

function sortEventList(list, sortType) {
    if (sortType == "name") {
        list.sort(function(a, b) { return a.name.localeCompare(b.name); });
    } else {
        list.sort(function(a, b) {
            let dateA = a.date.split("/");
            let dateB = b.date.split("/");
            return new Date(dateA[2], dateA[1] - 1, dateA[0]) - new Date(dateB[2], dateB[1] - 1, dateB[0]);
        });
    }
    return list;
}

function searchEvents() {
    let input = document.getElementById("searchEvent");
    let sort = document.getElementById("sortEvents");
    if (!input || !sort) return;
    let searchText = input.value.toLowerCase();
    let filtered = events.filter(function(event) {
        return event.category == "technical" && event.name.toLowerCase().includes(searchText);
    });
    currentPage = 1;
    showEvents(sortEventList(filtered, sort.value));
}

function showEvents(list) {
    let box = document.getElementById("technicalEvents");
    let pagination = document.getElementById("pagination");
    if (!box || !pagination) return;
    box.innerHTML = "";
    pagination.innerHTML = "";
    if (list.length == 0) { box.innerHTML = "<p>No events found.</p>"; return; }
    let totalPages = Math.ceil(list.length / eventsPerPage);
    currentPage = Math.min(currentPage, totalPages);
    let pageEvents = list.slice((currentPage - 1) * eventsPerPage, currentPage * eventsPerPage);
    pageEvents.forEach(function(event) {
        box.innerHTML += `<div class="tech-card"><h2>${event.name}</h2><p><strong>Date : </strong>${event.date}<br><strong>Time : </strong>${event.time}<br><strong>Venue : </strong>${event.venue}<br><strong>Last Date to Register : </strong>${event.lastDate}<br></p><button onclick="registerEvent('${event.name}','${event.date}')">Register Now</button></div><br><br>`;
    });
    for (let i = 1; i <= totalPages; i++) {
        pagination.innerHTML += `<button onclick="changePage(${i})">${i}</button> `;
    }
}

function changePage(page) {
    currentPage = page;
    let input = document.getElementById("searchEvent");
    let sort = document.getElementById("sortEvents");
    if (!input || !sort) return;
    let searchText = input.value.toLowerCase();
    let filtered = events.filter(function(event) {
        return event.category == "technical" && event.name.toLowerCase().includes(searchText);
    });
    showEvents(sortEventList(filtered, sort.value));
}

function searchNonTechnicalEvents() {
    let input = document.getElementById("searchEvent");
    let sort = document.getElementById("sortEvents");
    if (!input || !sort) return;
    let searchText = input.value.toLowerCase();
    let filtered = events.filter(function(event) {
        return event.category == "non-technical" && event.name.toLowerCase().includes(searchText);
    });
    nonTechnicalPage = 1;
    showNonTechnicalEvents(sortEventList(filtered, sort.value));
}

function showNonTechnicalEvents(list) {
    let box = document.getElementById("nonTechnicalEvents");
    let pagination = document.getElementById("pagination");
    if (!box || !pagination) return;
    box.innerHTML = "";
    pagination.innerHTML = "";
    if (list.length == 0) { box.innerHTML = "<p>No events found.</p>"; return; }
    let totalPages = Math.ceil(list.length / eventsPerPage);
    nonTechnicalPage = Math.min(nonTechnicalPage, totalPages);
    let pageEvents = list.slice((nonTechnicalPage - 1) * eventsPerPage, nonTechnicalPage * eventsPerPage);
    pageEvents.forEach(function(event) {
        box.innerHTML += `<div class="tech-card"><h2>${event.name}</h2><p><strong>Date : </strong>${event.date}<br><strong>Time : </strong>${event.time}<br><strong>Venue : </strong>${event.venue}<br><strong>Last Date to Register : </strong>${event.lastDate}<br></p><button onclick="registerEvent('${event.name}','${event.date}')">Register Now</button></div><br><br>`;
    });
    for (let i = 1; i <= totalPages; i++) {
        pagination.innerHTML += `<button onclick="changeNonTechnicalPage(${i})">${i}</button> `;
    }
}

function changeNonTechnicalPage(page) {
    nonTechnicalPage = page;
    let input = document.getElementById("searchEvent");
    let sort = document.getElementById("sortEvents");
    if (!input || !sort) return;
    let searchText = input.value.toLowerCase();
    let filtered = events.filter(function(event) {
        return event.category == "non-technical" && event.name.toLowerCase().includes(searchText);
    });
    showNonTechnicalEvents(sortEventList(filtered, sort.value));
}

function registerEvent(eventName, eventDate) {
    var formData = new FormData();
    formData.append("event_name", eventName);
    fetch("register_event.php", { method: "POST", body: formData })
        .then(function(response) {
            if (response.status === 401) {
                window.location.href = "../Before%20Login/Login.php?error=unauthorized";
                throw new Error("Please log in first.");
            }
            return response.json();
        })
        .then(function(data) {
            alert(data.message);
            if (data.success) loadMyRegistrations();
        })
        .catch(function(error) {
            if (error.message !== "Please log in first.") alert("Unable to register. Please try again.");
        });
}

function loadMyRegistrations() {
    var table = document.getElementById("registeredTable");
    if (!table) return;
    fetch("my_registrations.php")
        .then(function(response) {
            if (response.status === 401) {
                table.innerHTML = '<tr><th>Event Name</th><th>Date</th><th>Registered</th></tr><tr><td colspan="3">Please log in to view your registered events.</td></tr>';
                return null;
            }
            return response.json();
        })
        .then(function(data) {
            if (!data) return;
            table.innerHTML = "<tr><th>Event Name</th><th>Date</th><th>Registered</th></tr>";
            if (!data.success || data.registrations.length === 0) {
                var row = table.insertRow();
                row.insertCell(0).colSpan = 3;
                row.cells[0].textContent = "You have not registered for any events yet.";
                return;
            }
            data.registrations.forEach(function(item) {
                var row = table.insertRow();
                row.insertCell(0).textContent = item.title;
                row.insertCell(1).textContent = item.event_date;
                row.insertCell(2).textContent = "✓ Registered";
            });
        })
        .catch(function() {
            if (table) {
                table.innerHTML = '<tr><th>Event Name</th><th>Date</th><th>Registered</th></tr><tr><td colspan="3">Unable to load registrations.</td></tr>';
            }
        });
}

if (document.getElementById("registeredTable")) loadMyRegistrations();
