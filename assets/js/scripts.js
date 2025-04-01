/**
 * Equipment Alarm System - Client-side JavaScript
 */

// Wait for the document to be fully loaded
document.addEventListener("DOMContentLoaded", function () {
    // Enable Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Enable Bootstrap popovers
    const popoverTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="popover"]')
    );
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Filter alarms in the activated alarms table
    const alarmFilter = document.getElementById("alarm-filter");
    if (alarmFilter) {
        alarmFilter.addEventListener("keyup", function () {
            const filterValue = this.value.toLowerCase();
            const table = document.getElementById("activated-alarms-table");
            const rows = table.getElementsByTagName("tr");

            // Loop through all table rows except the header
            for (let i = 1; i < rows.length; i++) {
                const descriptionCol = rows[i].getElementsByTagName("td")[4]; // Alarm description column
                if (descriptionCol) {
                    const textValue =
                        descriptionCol.textContent || descriptionCol.innerText;
                    if (textValue.toLowerCase().indexOf(filterValue) > -1) {
                        rows[i].style.display = "";
                    } else {
                        rows[i].style.display = "none";
                    }
                }
            }
        });
    }

    // Table sorting functionality
    const sortableTables = document.querySelectorAll(".sortable-table");
    sortableTables.forEach(function (table) {
        const headers = table.querySelectorAll("th.sortable");
        headers.forEach(function (header, index) {
            header.addEventListener("click", function () {
                sortTable(table, index);
            });
        });
    });

    // Confirm deletion dialogs
    const deleteButtons = document.querySelectorAll(".btn-delete");
    deleteButtons.forEach(function (button) {
        button.addEventListener("click", function (e) {
            if (
                !confirm(
                    "Are you sure you want to delete this item? This action cannot be undone."
                )
            ) {
                e.preventDefault();
            }
        });
    });

    // Activate/Deactivate alarm confirmation
    const statusToggleButtons = document.querySelectorAll(".btn-toggle-status");
    statusToggleButtons.forEach(function (button) {
        button.addEventListener("click", function (e) {
            const action = this.dataset.action;
            const alarmId = this.dataset.id;
            const alarmDesc = this.dataset.desc;

            let confirmMessage = "";
            if (action === "activate") {
                confirmMessage = `Activate alarm "${alarmDesc}"?`;
            } else {
                confirmMessage = `Deactivate alarm "${alarmDesc}"?`;
            }

            if (!confirm(confirmMessage)) {
                e.preventDefault();
            }
        });
    });
});

/**
 * Sort table by clicking on headers
 *
 * @param {HTMLElement} table - The table to sort
 * @param {number} columnIndex - The column index to sort by
 */
function sortTable(table, columnIndex) {
    let rows,
        switching,
        i,
        x,
        y,
        shouldSwitch,
        dir,
        switchcount = 0;
    switching = true;
    // Set the sorting direction to ascending
    dir = "asc";

    while (switching) {
        switching = false;
        rows = table.rows;

        // Loop through all table rows except headers
        for (i = 1; i < rows.length - 1; i++) {
            shouldSwitch = false;

            x = rows[i].getElementsByTagName("td")[columnIndex];
            y = rows[i + 1].getElementsByTagName("td")[columnIndex];

            // Check if the two rows should switch
            if (dir == "asc") {
                if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                    shouldSwitch = true;
                    break;
                }
            } else if (dir == "desc") {
                if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                    shouldSwitch = true;
                    break;
                }
            }
        }

        if (shouldSwitch) {
            // If a switch is needed, make the switch and mark switching as done
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
            switchcount++;
        } else {
            // If no switching has been done AND the direction is "asc", set direction to "desc" and run while loop again
            if (switchcount == 0 && dir == "asc") {
                dir = "desc";
                switching = true;
            }
        }
    }

    // Update the headers to show sorting direction
    const headers = table.querySelectorAll("th.sortable");
    headers.forEach(function (header, index) {
        header.classList.remove("sorted-asc", "sorted-desc");
        if (index === columnIndex) {
            header.classList.add(dir === "asc" ? "sorted-asc" : "sorted-desc");
        }
    });
}
