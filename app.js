const submitBtn = document.getElementById("submit");
const errorMessage = document.querySelector(".warning");

function verifyCallback(res) {
  errorMessage.textContent = "";
  submitBtn.removeAttribute("disabled");
}

function expiredCallback() {
  grecaptcha.reset();
  errorMessage.textContent = "Zum Senden bitte Google reCAPTCHA ankreuzen.";
  submitBtn.setAttribute("disabled");
}
window.verifyCallback = verifyCallback;

const successMessage = document.getElementById("success_message");
let myForm = document.getElementById("form");

const handleSubmit = (e) => {
  e.preventDefault();
  let formData = new FormData(myForm);
  const response = grecaptcha.getResponse();

  if (response.length !== 0) {
    fetch("/alltours/anmeldeseite/post_verify.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams(formData).toString(),
    }).then(() => {
      successMessage.classList.add("show");
    });
  } else {
    errorMessage.textContent = "Zum Senden bitte Google reCAPTCHA ankreuzen.";
    return false;
  }
};
myForm.addEventListener("submit", handleSubmit);
