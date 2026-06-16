document.addEventListener("DOMContentLoaded", () => {
    const tabs = document.querySelectorAll(".tab");
    const contents = document.querySelectorAll(".tab-content");
    const editBtn = document.getElementById("editBtn");
    const saveBtn = document.getElementById("saveBtn"); // Now exists in PHP
    
    // Elements to toggle
    const viewElements = document.querySelectorAll(".view");
    const editElements = document.querySelectorAll(".edit-field");
    const profilePic = document.getElementById("profilePic");
    const imageInput = document.querySelector(".image-input");

    /* =====================
       TAB SWITCHING
    ===================== */
    tabs.forEach(tab => {
        tab.addEventListener("click", () => {
            // Remove active class from all
            tabs.forEach(t => t.classList.remove("active"));
            contents.forEach(c => c.classList.remove("active"));

            // Add active class to clicked
            tab.classList.add("active");
            document.getElementById(tab.dataset.tab).classList.add("active");
        });
    });

    /* =====================
       EDIT MODE TOGGLE
    ===================== */
    if(editBtn) {
        editBtn.addEventListener("click", () => {
            // Hide View elements, Show Edit inputs
            viewElements.forEach(el => el.style.display = "none");
            editElements.forEach(el => el.style.display = "block");
            
            // Swap Buttons
            editBtn.style.display = "none";
            if(saveBtn) saveBtn.style.display = "inline-block";
            
            // Enable image clicking
            profilePic.style.cursor = "pointer";
            profilePic.classList.add("editable");
        });
    }

    /* =====================
       IMAGE PREVIEW (Visual Only)
    ===================== */
    // Note: To actually save the image, the <form> in profile.php needs enctype="multipart/form-data"
    // For now, this just previews what you picked.
    if(profilePic && imageInput) {
        profilePic.addEventListener("click", () => {
            // Only trigger if we are in edit mode (simple check: if saveBtn is visible)
            if (saveBtn && saveBtn.style.display !== "none") {
                imageInput.click();
            }
        });

        imageInput.addEventListener("change", () => {
            const file = imageInput.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = () => {
                profilePic.src = reader.result;
            };
            reader.readAsDataURL(file);
        });
    }
});