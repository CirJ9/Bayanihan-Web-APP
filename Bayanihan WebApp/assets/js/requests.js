document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("form");
    const taskSelect = form.task;
    const otherTaskInput = form.other;

    /* =====================
       OTHER TASK LOGIC
    ===================== */
    // Disable "Other" input by default
    otherTaskInput.disabled = true;

    // Listen for dropdown changes
    taskSelect.addEventListener("change", () => {
        if (taskSelect.value === "other") {
            otherTaskInput.disabled = false;
            otherTaskInput.focus();
        } else {
            otherTaskInput.disabled = true;
            otherTaskInput.value = "";
        }
    });

    /* =====================
       CLIENT-SIDE VALIDATION (Visual Only)
    ===================== */
    form.addEventListener("submit", (e) => {
        // We DO NOT use e.preventDefault() here anymore.
        // We let the form submit naturally to the PHP server.
        
        let isValid = true;
        
        // Simple check for empty fields (optional, since 'required' attribute handles this mostly)
        if (taskSelect.value === "other" && !otherTaskInput.value.trim()) {
            alert("Please specify the task description.");
            e.preventDefault(); // Stop only if validation fails
            return;
        }

        // If valid, the browser sends data to request.php
    });
});