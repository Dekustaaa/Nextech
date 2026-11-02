const profileForm = document.getElementById("profileForm");

const usernameInput = document.getElementById("username");
const emailInput = document.getElementById("email");
const phoneInput = document.getElementById("phone");
const passwordInput = document.getElementById("password");

const message = document.getElementById("message");


const defaultProfile = {
    username: "username",
    email: "user@gmail.com",
    phone: ""
};

function getProfile() {
    const savedProfile = localStorage.getItem("nexatechProfile");

    if (savedProfile) {
        return {
            ...defaultProfile,
            ...JSON.parse(savedProfile)
        };
    }

    return { ...defaultProfile };
}


function displayProfile(profile) {

    usernameInput.value = profile.username;
    emailInput.value = profile.email;
    phoneInput.value = profile.phone || "";

    document.getElementById("displayUsername").textContent =
        profile.username;

    document.getElementById("displayEmail").textContent =
        profile.email;
}


displayProfile(getProfile());


function showError(field, text) {

    const input = document.getElementById(field);
    const error = document.getElementById(field + "Error");

    input.classList.toggle("invalid", text !== "");
    error.textContent = text;
}


function clearErrors() {

    const fields = [
        "username",
        "email",
        "phone",
        "password"
    ];

    fields.forEach(field => {
        showError(field, "");
    });
}


profileForm.addEventListener("submit", function(event) {

    event.preventDefault();

    clearErrors();

    message.textContent = "";
    message.className = "";

    const username = usernameInput.value.trim();
    const email = emailInput.value.trim();
    const phone = phoneInput.value.trim();
    const password = passwordInput.value;

    let valid = true;


    if (username.length < 3) {

        showError(
            "username",
            "Username minimal 3 karakter."
        );

        valid = false;
    }


    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!emailPattern.test(email)) {

        showError(
            "email",
            "Masukkan email yang valid."
        );

        valid = false;
    }


    const phonePattern = /^[0-9+\-\s()]{8,18}$/;

    if (phone !== "" && !phonePattern.test(phone)) {

        showError(
            "phone",
            "Nomor telepon tidak valid."
        );

        valid = false;
    }

    if (password !== "" && password.length < 8) {

        showError(
            "password",
            "Password minimal 8 karakter."
        );

        valid = false;
    }


    if (!valid) {

        message.textContent =
            "Periksa kembali data yang kamu masukkan.";

        message.classList.add("error");

        return;
    }


    const updatedProfile = {
        username: username,
        email: email,
        phone: phone
    };

    localStorage.setItem(
        "nexatechProfile",
        JSON.stringify(updatedProfile)
    );

    displayProfile(updatedProfile);

    passwordInput.value = "";


    message.textContent =
        "Profil berhasil diperbarui!";

    message.classList.add("success");

});


document.getElementById("togglePassword")
.addEventListener("click", function() {

    if (passwordInput.type === "password") {

        passwordInput.type = "text";
        this.textContent = "Sembunyikan";

    } else {

        passwordInput.type = "password";
        this.textContent = "Lihat";

    }

});


document.getElementById("languageBtn")
.addEventListener("click", function() {

    this.textContent =
        this.textContent === "ID" ? "DE" : "ID";

});


document.getElementById("searchInput")
.addEventListener("keydown", function(event) {

    if (event.key === "Enter" && this.value.trim() !== "") {

        localStorage.setItem(
            "nexatechSearch",
            this.value.trim()
        );

        window.location.href = "main.html#produk";

    }

});
