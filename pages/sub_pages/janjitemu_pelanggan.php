<?php if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (
    !isset($_SESSION['logged_in']) ||
    $_SESSION['logged_in'] !== true ||
    !isset($_SESSION['role']) ||
    ($_SESSION['role'] !== 'pelanggan' && $_SESSION['role'] !== 'juruteknik')
) {
    http_response_code(401);
    include($_SERVER['DOCUMENT_ROOT'] . '/SolusiTemu/pages/errors/401.html');
    exit;
}

$allServices = db_getAllServices($pdo);
// Sub-category status e.g. HW.XXX.BROKEN
$hardwareList = $allServices['hardware'] ?? [];
$softwareList = $allServices['software'] ?? [];

?>

<link rel="stylesheet" href="../public/css/appointment_customer.css">
<!-- SERVER will get status, idService, brandName, modalName by POST method -->
<form class="appointment-container" id="appointment-container" method="POST" action="api/make_appointment.php">
    <input type="hidden" name="status" value="making-appointment">
    <input type="hidden" name="idService" value="">
    <input type="hidden" name="brandName" value="">
    <input type="hidden" name="modalName" value="">
    <input type="hidden" name="date" value="">
    <input type="hidden" name="time" value="">
    <!-- Header area -->
    <div class="banner-container">
        <h1 class="font-oswald" style="margin: 0px;">Borang JanjiTemu</h1>
        <svg class="icons">
            <use href="../public/icons.svg#tool-icon"></use>
        </svg>
    </div>
    <!-- Choices area -->
    <label class="label">Masalah</label>
    <!-- Search problem area -->
    <div class="search-container">
        <input class="search-bar" name="searching" type="search" placeholder="cth: Komputer saya sering crash...">
        <svg class="icons">
            <use href="../public/icons.svg#search-icon"></use>
        </svg>
    </div>
    <div class="services-container">
        <!-- Hardware Services -->
        <div class="hardware-services">
            <label class="label">Hardware</label>
            <?php foreach ($hardwareList as $subCat => $servicesList): ?>
                <!-- Label of Sub-Categories -->
                <div class="subCat">
                    <label class="label" onclick="expandServices(this)">
                        <svg class="icons chevron">
                            <use href="../public/icons.svg#chevron-right-icon"></use>
                        </svg>
                        <span>
                            <?= htmlspecialchars($subCat) ?>
                        </span>
                    </label>
                </div>
                <!-- Choices -->
                <div class="activities-container">
                    <?php foreach ($servicesList as $item): ?>
                        <button type="button" class="activities-btn font-oswald"
                            data-id="<?= htmlspecialchars($item['idService']) ?>" onclick="selectService(this)">
                            • <?= strtolower(htmlspecialchars($item['serviceActivity'])) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <!-- Software Services -->
        <div class="software-services">
            <label class="label">Software</label>
            <?php foreach ($softwareList as $subCat => $servicesList): ?>
                <!-- Label of Sub-Categories -->
                <div class="subCat">
                    <label class="label" onclick="expandServices(this)">
                        <svg class="icons chevron">
                            <use href="../public/icons.svg#chevron-right-icon"></use>
                        </svg>
                        <span>
                            <?= htmlspecialchars($subCat) ?>
                        </span>
                    </label>
                </div>
                <!-- Choices -->
                <div class="activities-container">
                    <?php foreach ($servicesList as $item): ?>
                        <button type="button" class="activities-btn font-oswald"
                            data-id="<?= htmlspecialchars($item['idService']) ?>" onclick="selectService(this)">
                            • <?= strtolower(htmlspecialchars($item['serviceActivity'])) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <!-- Divice brand -->
    <label class="label">Jenama</label>
    <!-- Search brand & model area -->
    <div class="search-container">
        <input class="search-bar" name="searching" type="search" placeholder="cth: HP OMEN 16 Gaming">
        <svg class="icons">
            <use href="../public/icons.svg#search-icon"></use>
        </svg>
    </div>
    <!-- Brands container -->
    <div class="brands-container">

    </div>
    <!-- Divice modals -->
    <label class="label">Modal</label>
    <div class="modals-container">
        <span class="label">----- Tiada jenama yang terpilih -----</span>
    </div>
    <!-- Date and time -->
    <label class="label">Tarikh dan Masa</label>
    <!-- Calendar -->
    <div class="calendar">
        <!-- Year, month and icons -->
        <div class="year-month">
            <svg class="icons chevron" id="prev-btn">
                <use href="../public/icons.svg#chevron-left-icon"></use>
            </svg>
            <div id="year-month-label" class="label"></div>
            <svg class="icons chevron" id="next-btn">
                <use href="../public/icons.svg#chevron-right-icon"></use>
            </svg>
        </div>
        <!-- Day name label -->
        <div class="days-label">
            <div class="label">Ahad</div>
            <div class="label">Isnin</div>
            <div class="label">Selasa</div>
            <div class="label">Rabu</div>
            <div class="label">Khamis</div>
            <div class="label">Jumaat</div>
            <div class="label">Sabtu</div>
        </div>
        <!-- Date -->
        <div class="date-container">

        </div>
        <!-- Time -->
        <div class="time-container">

        </div>
    </div>
    <button type="button" class="reset-btn label">Reset</button>
    <button class="submit-btn">Selesai</button>
</form>
<!-- JavaScripts -->
<script>
    // Expand services panel
    function expandServices(element) {
        // Self 
        const mainCat = element.closest('.label');
        if (!mainCat) {
            return;
        }
        // Element's parent container(.subCat)
        const services = mainCat.closest('.subCat');
        if (!services) {
            return;
        }
        // Next container of subCat (.activitie-container) 
        const serviceActivities = services.nextElementSibling;
        if (!serviceActivities || !serviceActivities.classList.contains('activities-container')) {
            return;
        }

        // Business logic
        if (serviceActivities.style.display === 'flex') {
            serviceActivities.style.display = 'none';
            mainCat.querySelector('.chevron').style.transform = 'rotate(0deg)';
            mainCat.style.color = '';
        } else {
            document.querySelectorAll('.activities-container').forEach(container => {
                container.style.display = 'none';
            });

            document.querySelectorAll('.subCat .label').forEach(label => {
                const chevron = label.querySelector('.chevron');
                if (chevron) chevron.style.transform = 'rotate(0deg)';
                label.style.color = '';
            });

            serviceActivities.style.display = 'flex'
            mainCat.querySelector('.chevron').style.transform = 'rotate(90deg)';
            mainCat.style.color = '#f0c369';

        }
    }

    // Service's activities/status
    function selectService(element) {
        const serviceId = element.dataset.id;
        const currentForm = element.form;
        if (!currentForm) return;
        const selectedServiceId = currentForm.querySelector('input[name="idService"]');

        if (serviceId && selectedServiceId && selectedServiceId.value === serviceId) {
            selectedServiceId.value = '';
            element.style.color = '';
            element.style.transform = "scale(1)";
            element.style.boxShadow = "none";
        } else {
            if (selectedServiceId) {
                selectedServiceId.value = serviceId;
            }
            document.querySelectorAll('.activities-btn').forEach(btn => {
                if (btn !== element) {
                    btn.style.color = '';
                    btn.style.transform = "scale(1)";
                    btn.style.boxShadow = "none";
                }
            });
            element.style.color = '#f0c369';
            element.style.transform = "scale(0.95)";
            element.style.boxShadow = "inset 4px 4px 10px rgba(0,0,0,0.3)";
        }
    }

    // Function get all brands and modals from JSON file
    // Initial variable 
    let allBrandsModalsData = null;
    // Asynchronous function
    async function getData() {
        try {
            // Data in JSON form
            const response = await fetch('../public/data/brands_models.json');
            // Data in readable state for JavaScripts
            const data = await response.json();
            allBrandsModalsData = data;
            displayBrands();
        } catch (error) {
            console.log('Error! Please wait a while.', error);
        }
    }

    // Function to display all brands at the front-end
    function displayBrands() {
        // Get keys of array (e.g. ASUS, DELL)
        brands = Object.keys(allBrandsModalsData);
        const brandsContainer = document.querySelector('.brands-container');
        let htmlBrandContainer = '';

        brands.forEach(function (brandName) {
            htmlBrandContainer += `
                <button type="button" class="brand" data-brand="${brandName}" onclick="selectBrand(this)">
                    <span class="label">${brandName}</span>
                </button>
            `;
        });
        brandsContainer.innerHTML = htmlBrandContainer;
    }

    // Selected brand display and value
    function selectBrand(element) {
        const brandName = element.dataset.brand;
        const currentForm = element.form;
        if (!currentForm) return;
        const selectedBrandName = currentForm.querySelector('input[name="brandName"]');
        const selectedModalName = currentForm.querySelector('input[name="modalName"]');
        const modalsContainer = document.querySelector('.modals-container');
        const brandText = document.querySelectorAll('.brand .label');
        let htmlModalsContainer = '<span class="label">----- Tiada jenama yang terpilih -----</span>';

        if (brandName && selectedBrandName && selectedBrandName.value === brandName) {
            selectedBrandName.value = '';
            selectedModalName.value = '';
            element.style.color = '';
            element.style.transform = "scale(1)";
            element.style.border = '2px solid #474747';
            element.style.boxShadow = "";
            modalsContainer.innerHTML = htmlModalsContainer;

        } else {
            if (selectedBrandName) {
                selectedBrandName.value = brandName;
            }
            document.querySelectorAll('.brands-container .brand').forEach(btn => {
                if (btn !== element) {
                    btn.style.color = '';
                    btn.style.transform = "scale(1)";
                    btn.style.border = '2px solid #474747';
                    btn.style.boxShadow = "";
                }
            });
            element.style.border = '3px solid #f0c369';
            element.style.transform = "scale(0.95)";
            element.style.boxShadow = "0 0 15px 1px rgba(240, 195, 105, 0.3)";
            displayModals(brandName, currentForm);
        }
    }

    // Display modals with selected brand
    function displayModals(brand, currentForm) {
        const modals = allBrandsModalsData[brand];
        const modalsContainer = document.querySelector('.modals-container');
        let htmlModalsContainer = '';

        modals.forEach(function (modalName) {
            htmlModalsContainer += `
                <button type="button" class="modal label" onclick="selectModal(this)" data-modal="${modalName}">• ${modalName}</button>
            `;
        });
        modalsContainer.innerHTML = htmlModalsContainer;
    }

    // Selected modal display and value
    function selectModal(element) {
        const modalName = element.dataset.modal;
        const currentForm = element.form;
        if (!currentForm) return;
        const selectedModalName = currentForm.querySelector('input[name="modalName"]');

        if (modalName && selectedModalName && selectedModalName.value === modalName) {
            selectedModalName.value = '';
            element.style.color = '';
            element.style.transform = "scale(1)";
            element.style.boxShadow = "none";
        } else {
            if (selectedModalName) {
                selectedModalName.value = modalName;
            }
            document.querySelectorAll('.modal').forEach(btn => {
                if (btn !== element) {
                    btn.style.color = '';
                    btn.style.transform = "scale(1)";
                    btn.style.boxShadow = "none";
                }
            });
            element.style.color = '#f0c369';
            element.style.transform = "scale(0.95)";
            element.style.boxShadow = "inset 4px 4px 10px rgba(0,0,0,0.3)";
        }
    }

    // Calendar rendering
    const yearMonthLabel = document.getElementById('year-month-label');
    const dateContainer = document.querySelector('.date-container');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const resetBtn = document.querySelector('.reset-btn');

    let currentDate = new Date();

    function renderCalendar() {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();

        const months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
        const monthName = months[month];
        yearMonthLabel.innerHTML = `${year} ${monthName}`;

        const firstDayIndex = new Date(year, month, 1).getDay();
        const totalDays = new Date(year, month + 1, 0).getDate();

        dateContainer.innerHTML = '';

        for (let i = 0; i < firstDayIndex; i++) {
            const emptyDiv = document.createElement('div');
            emptyDiv.classList.add('empty');
            dateContainer.appendChild(emptyDiv);
        }
        const today = new Date();
        for (let day = 1; day <= totalDays; day++) {
            const dateBtn = document.createElement('button');
            dateBtn.type = 'button';
            dateBtn.classList.add('days', 'label');
            dateBtn.innerText = day;
            dateBtn.dataset.date = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

            const compareDate = new Date(year, month, day, 23, 59, 59);
            if (compareDate < today) {
                dateBtn.disabled = true;
                dateBtn.style.opacity = "0.3";
                dateBtn.style.cursor = "not-allowed";
            }

            if (
                day === today.getDate() &&
                month === today.getMonth() &&
                year === today.getFullYear()
            ) {
                dateBtn.classList.add('today');
            }

            if (selectedDate && selectedDate.value === dateBtn.dataset.date) {
                dateBtn.style.border = "2px solid #d9a441";
            }

            dateBtn.onclick = function () {
                selectDate(this);
            };

            dateContainer.appendChild(dateBtn);
        }
    }

    prevBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar();
    });

    nextBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar();
    });

    // Selected Date value
    const selectedDate = document.querySelector('input[name="date"]');
    function selectDate(element) {
        const targetDate = element.dataset.date;
        if (selectedDate.value === targetDate) {
            selectedDate.value = '';
            element.style.color = '';
            element.style.border = "none";
        } else {
            document.querySelectorAll('.date-container .days').forEach(btn => {
                btn.style.color = '';
                btn.style.border = "none";
            });

            selectedDate.value = targetDate;
            element.style.border = "2px solid #d9a441";
        }
        if (selectedTime) selectedTime.value = '';
        renderTime();
    }

    // Display available time
    const selectedTime = document.querySelector('input[name="time"]');
    const timeContainer = document.querySelector('.time-container');
    const availableSlots = ['09:00', '10:30', '12:00', '14:00', '15:30', '17:00'];
    function renderTime() {
        let htmlTimesContainer = '<label class="label" style="width: 100%;margin:10px 10px;">Pilih Masa</label>';
        const now = new Date();
        const currentHour = now.getHours();
        const currentMinute = now.getMinutes();
        const todayString = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;

        availableSlots.forEach(time => {
            const [slotHour, slotMinute] = time.split(':').map(Number);

            let disabledAttr = '';
            let activeStyle = '';

            if (!selectedDate.value || (selectedDate.value === todayString && (slotHour < currentHour || (slotHour === currentHour && slotMinute <= currentMinute)))) {
                disabledAttr = 'disabled style="opacity: 0.3; cursor: not-allowed;"';
            }

            if (selectedTime && selectedTime.value === time && !disabledAttr) {
                activeStyle = 'style="color: #f0c369; transform: scale(0.95); box-shadow: inset 4px 4px 10px rgba(0,0,0,0.3);"';
            }
            htmlTimesContainer += `<button type="button" class="time label" onclick="selectTime(this)" data-time="${time}" ${disabledAttr} ${activeStyle}>${time}</button>`;
        });

        timeContainer.innerHTML = htmlTimesContainer;
    }

    // Select time
    function selectTime(element) {
        const targetTime = element.dataset.time;
        if (selectedTime.value === targetTime) {
            selectedTime.value = '';
            element.style.color = '';
            element.style.transform = "scale(1)";
            element.style.boxShadow = "none";
        } else {
            timeContainer.querySelectorAll('button').forEach(btn => {
                btn.style.color = '';
                btn.style.transform = "scale(1)";
                btn.style.boxShadow = "none";
            });
            selectedTime.value = targetTime;
            element.style.color = '#f0c369';
            element.style.transform = "scale(0.95)";
            element.style.boxShadow = "inset 4px 4px 10px rgba(0,0,0,0.3)";
        }
        renderTime();
    }

    resetBtn.addEventListener('click', () => {
        currentDate = new Date();
        if (selectedDate) {
            selectedDate.value = '';
        }
        if (selectedTime) {
            selectedTime.value = '';
        }
        renderTime();
        renderCalendar();
    });

    // Call the getData() function after all html rendering successful
    document.addEventListener("DOMContentLoaded", getData, { once: true });
    renderCalendar();
    renderTime();
</script>