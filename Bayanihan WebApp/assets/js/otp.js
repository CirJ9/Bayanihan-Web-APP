let OTP = "";
const otpForm = document.querySelector(".otp-form"),
  email = document.getElementById("email"),
  verifyEmail = document.getElementById("verifyEmail"),
  inputs = document.querySelectorAll(".otp-form input"),
  step1 = document.querySelector(".step1"),
  step2 = document.querySelector(".step2"),
  step3 = document.querySelector(".step3"),
  nextButton = document.querySelector(".nextButton"),
  verifyButton = document.querySelector(".verifyButton");

window.addEventListener("load", () => {
  // 1. Initialize with your NEW Public Key
  emailjs.init("vkQ9ajA3Nof2t1Kuq"); 

  step2.style.display = "none";
  step3.style.display = "none";
  nextButton.classList.add("disable");
  verifyButton.classList.add("disable");
});

const generateOTP = () => {
  return Math.floor(1000 + Math.random() * 9000);
};

const validateEmail = (email) => {
  var re = /\S+@\S+\.\S+/;
  if (re.test(email)) {
    nextButton.classList.remove("disable");
  } else {
    nextButton.classList.add("disable");
  }
};

inputs.forEach((input) => {
  input.addEventListener("keyup", function (e) {
    if (this.value.length >= 1) {
      e.target.value = e.target.value.substr(0, 1);
    }

    if (
      inputs[0].value != "" &&
      inputs[1].value != "" &&
      inputs[2].value != "" &&
      inputs[3].value != ""
    ) {
      verifyButton.classList.remove("disable");
    } else {
      verifyButton.classList.add("disable");
    }
  });
});

/* --- SEND OTP LOGIC --- */
nextButton.addEventListener("click", () => {
  nextButton.innerHTML = "&#9889; Sending...";
  OTP = generateOTP();
  verifyEmail.innerHTML = email.value;

  // 2. THE KITCHEN SINK PARAMETERS
  // We send the email to ALL common variable names to ensure it hits the right one.
  var templateParams = {
    // Recipient Fields (One of these WILL work)
    to_email: email.value,    
    email: email.value,       
    reply_to: email.value,   
    recipient: email.value,   

    // Content Fields
    to_name: "jhonricbaguisi@gmail.com",     // Just in case template expects a name
    from_name: "Bayanihan App",
    OTP: "111111",
    message: "Your verification code is " + OTP, // Uncommented this just in case
    time: 30
  };

  const serviceID = "service_wkbsw38";
  const templateID = "template_oy6b70g";

  emailjs.send(serviceID, templateID, templateParams).then(
    () => {
      // Success
      alert("OTP Sent to " + email.value);
      nextButton.innerHTML = "Next &rarr;";
      step1.style.display = "none";
      step2.style.display = "block";
      step3.style.display = "none";
    },
    (err) => {
      // Error
      nextButton.innerHTML = "Next &rarr;";
      console.log(JSON.stringify(err));
      alert("Error sending email: " + JSON.stringify(err));
    }
  );
});

/* --- VERIFY OTP LOGIC --- */
verifyButton.addEventListener("click", () => {
  let verify = ""; // Fixed the space issue
  
  inputs.forEach((input) => {
    verify += input.value;
  });

  if (OTP == verify) {
    // 1. Visual Success
    step1.style.display = "none";
    step2.style.display = "none";
    step3.style.display = "block";
    
    // 2. Redirect to PHP to update database
    setTimeout(() => {
        window.location.href = "otp.php?verified=true"; 
    }, 1500);

  } else {
    verifyButton.classList.add("error-shake");
    alert("Invalid Code");
    setTimeout(() => {
      verifyButton.classList.remove("error-shake");
    }, 1000);
  }
});

function changeMyEmail() {
  step1.style.display = "block";
  step2.style.display = "none";
  step3.style.display = "none";
}