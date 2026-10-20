let currentDate = new Date();

function generateCalendar(){

    let year = currentDate.getFullYear();

    let month = currentDate.getMonth();

    let firstDay = new Date(year, month, 1).getDay();

    let lastDate = new Date(year, month + 1, 0).getDate();

    let monthNames = [
        "January",
        "February",
        "March",
        "April",
        "May",
        "June",
        "July",
        "August",
        "September",
        "October",
        "November",
        "December"
    ];

    document.getElementById("month-year").innerHTML =
        monthNames[month] + " " + year;


    let calendarBody = document.getElementById("calendar-body");

    calendarBody.innerHTML = "";


    let date = 1;


    for(let row = 0; row < 6; row++){

        let tr = document.createElement("tr");


        for(let col = 0; col < 7; col++){

            let td = document.createElement("td");


            if(row === 0 && col < firstDay){

                td.innerHTML = "";

            }

            else if(date > lastDate){

                td.innerHTML = "";

            }

            else{

                td.innerHTML = date;


                let today = new Date();


                if(
                    date === today.getDate() &&
                    month === today.getMonth() &&
                    year === today.getFullYear()
                ){

                    td.classList.add("today");

                }


                date++;

            }


            tr.appendChild(td);

        }


        calendarBody.appendChild(tr);


        if(date > lastDate){

            break;

        }

    }

}


function previousMonth(){

    currentDate.setMonth(currentDate.getMonth() - 1);

    generateCalendar();

}


function nextMonth(){

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

    document.getElementById("announcement-text").innerHTML =
        announcements[announcementIndex];

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

    index = number;

    document.getElementById("slide").src = images[index];

}

setInterval(function() {

    index++;

    if(index >= images.length) {
        index = 0;
    }

    document.getElementById("slide").src = images[index];

}, 3000);

function login() {
    window.location.href = "../After Login/Homepage.html?login=success";
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
    var menu = document.getElementById("navbar");

    if (menu.classList.contains("active")) {
        menu.classList.remove("active");
    } else {
        menu.classList.add("active");
    }
}

function toggleTheme() {
    document.body.classList.toggle("dark-mode");

    var button = document.querySelector(".theme-button");

    if (document.body.classList.contains("dark-mode")) {
        button.innerHTML = "☀️";
    } else {
        button.innerHTML = "🌙";
    }
}