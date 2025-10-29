document.addEventListener("DOMContentLoaded", () => {

  const emailInput = document.getElementById("signup_email");
  const emailPreview = document.getElementById("email-preview");
  
  const updateEmailPreview = () => {
    const email = emailInput.value.trim();
    emailPreview.textContent = email ? `${email.toLowerCase()}@phinmaed.com` : "";
  };
  
  emailInput.addEventListener("input", updateEmailPreview);


    // function showError(message, formSelector = "#login-form") {
    //   console.log("Error target:", formSelector);
    //   const form = document.querySelector(formSelector);
    //   if (!form) return;

    //   const existing = form.querySelector('.error-box');
    //   if (existing) existing.remove();

    //   const errorBox = document.createElement('div');
    //   errorBox.className = 'error-box';
    //   errorBox.textContent = message;
    //   form.appendChild(errorBox);
    // }

    function showError(message, formSelector = "#login-form") {
      const form = document.querySelector(formSelector);
      if (!form) return;

      const existing = form.querySelector('.error-box');
      if (existing) existing.remove();

      const errorBox = document.createElement('div');
      errorBox.className = 'error-box';

      // Check if message is OTP-related
      if (message.includes("verify your account via OTP")) {
        const span = document.createElement('span');
        span.textContent = message + " ";

        const link = document.createElement('span');
        link.textContent = "Verify now";
        link.style.fontWeight = "bold";
        link.style.color = "inherit";
        link.style.textDecoration = "underline";
        link.style.cursor = "pointer";
        link.onclick = () => {
          window.location.href = "/assets/server/verify-otp.php";
        };
      
        errorBox.appendChild(span);
        errorBox.appendChild(link);
      } else {
        errorBox.textContent = message;
      }

      form.appendChild(errorBox);
    }

    
    const loginValidation = new JustValidate("#login-form", {
      errorLabelStyle: {
        color: "#d0413a",
        fontSize: "0.8rem",
        marginTop: "4px",
        display: "block"
      },
      focusInvalidField: true,
      lockForm: true
    });

    loginValidation
        .addField("#login_role", [
            {
                rule: 'required',
                errorMessage: "Please select your account type"
            }
        ])
        .addField("#login_email", [
            {
                rule: 'required',
                errorMessage: "Email is required"
            },
            {
                rule: 'email',
                errorMessage: "Invalid email"
            }
        ])
        .addField("#login-password", [
            {
                rule: 'required',
                errorMessage: "Password is required"
            },
            {
                rule: 'minLength',
                value: 8,
                errorMessage: "Password is at least 8 characters"
            },
            {
                validator: (value) => {
                    const hasUpperCase = /[A-Z]/.test(value);
                    return hasUpperCase;
                },
                errorMessage: "Password has at least one upper case character"
            },
            {
                validator: (value) => {
                    const hasLowerCase = /[a-z]/.test(value);
                    return hasLowerCase;
                },
                errorMessage: "Password has at least one lower case character"
            },
            {
                validator: (value) => {
                    const hasNumber = /[0-9]/.test(value);
                    return hasNumber;
                },
                errorMessage: "Password has at least one number"
            }
        ])
        .onSuccess((event) => {
        const formData = new FormData(event.target);
        console.log("Fade-out triggered");

        fetch('/assets/server/login-process.php', {
          method: 'POST',
          body: formData,
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            const card = document.getElementById('login-card');
            const welcome = document.getElementById('welcome-screen');

            document.getElementById('welcome-screen').classList.add('show');

            // Fade out the login card
            card.style.transition = 'opacity 0.5s ease';
            card.style.opacity = '0';

            // After card fades, show welcome screen
            setTimeout(() => {
              welcome.style.display = 'flex';
              welcome.style.opacity = '0';
              welcome.style.transform = 'scale(0.8)';
              welcome.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            
              // Trigger fade-in and zoom
              setTimeout(() => {
                welcome.style.opacity = '1';
                welcome.style.transform = 'scale(1)';
              }, 50);
            });
        
            // Redirect after animation
            setTimeout(() => {
              window.location.href = data.redirect || "home.php";
            }, 2000);
            } else {
                showError(data.message, "#login-form");
            }
        })
        .catch(() => {
          showError("Something went wrong, please try again.", "#login-form");
        });
      });

    console.log("Signup validator initialized")
    const signupValidation = new JustValidate("#signup-form", {
      errorLabelStyle: {
        color: "#d0413a",
        fontSize: "0.8rem",
        marginTop: "4px",
        display: "block"
      },
      focusInvalidField: true,
      lockForm: true
    });

    signupValidation
        .addField("#signup_role", [
          {
            rule: 'required',
            errorMessage: 'Please select your account type'
          }
        ])
        .addField("#department", [
          {
            rule: 'required',
            errorMessage: 'Please select your department'
          }
        ])
        .addField("#first_name", [
          {
            rule: 'required',
            errorMessage: 'First name is required'
          },
          {
            rule: 'maxLength',
            value: 30,
            errorMessage:'First name must be less than 30 characters'
          }
        ])
        .addField("#last_name", [
          {
            rule: 'required',
            errorMessage: 'Last name is required'
          },
          {
            rule: 'maxLength',
            value: 30,
            errorMessage:'Last name must be less than 30 characters'
          }
        ])
        .addField("#signup_email", [
          {
            rule: 'required',
            errorMessage: 'Username is required'
          },
          {
            rule: 'function',
            validator: (value) => {
              const fullEmail = value.trim().toLowerCase() + '@phinmaed.com';
              return /^[a-z0-9._%+-]+@phinmaed\.com$/.test(fullEmail);
            },
            errorMessage: 'Invalid username format (e.g. fnmi.lastname.up)'
          }
        ])
        .addField("#signup-password", [
          {
              rule: 'required',
              errorMessage: "Password is required"
          },
          {
              rule: 'minLength',
              value: 8,
              errorMessage: "Password is at least 8 characters"
          },
          {
              validator: (value) => {
                  const hasUpperCase = /[A-Z]/.test(value);
                  return hasUpperCase;
              },
              errorMessage: "Password has at least one upper case character"
          },
          {
              validator: (value) => {
                  const hasLowerCase = /[a-z]/.test(value);
                  return hasLowerCase;
              },
              errorMessage: "Password has at least one lower case character"
          },
          {
              validator: (value) => {
                  const hasNumber = /[0-9]/.test(value);
                  return hasNumber;
              },
              errorMessage: "Password has at least one number"
          }
        ])
        .addField("#password_confirmation", [
          {
            validator: (value, fields) => {
              return value === fields["#signup-password"].elem.value;
            },
            errorMessage: "Passwords do not match"
          }
        ])
      
        .onSuccess((event) => {
          const formData = new FormData(event.target);
          const username = formData.get('signup_email').trim().toLowerCase();
          formData.set('signup_email', username + '@phinmaed.com');
                
          return fetch('assets/server/signup-process.php', {
            method: 'POST',
            body: formData
          })
          .then(res => res.json())
          .then(data => {
            if (data.success){
              window.location.href = data.redirect || "index.php";
            } else {
              showError(data.message, "#signup-form");
            }
          })
          .catch((err) => {
            console.error("Signup fetch error:", err);
            showError("Something went wrong, please try again.", "#signup-form");
          });
        });

        console.log("Forgot validator initialized")
const forgotValidation = new JustValidate("#forgot-form");

forgotValidation
  .addField("#forgot_role", [
    {
      rule: 'required',
      errorMessage: "Please select your account type"
    }
  ])
  .addField("#forgot_email", [
    {
      rule: 'required',
      errorMessage: "Email is required"
    },
    {
      rule: 'email',
      errorMessage: "Invalid email format"
    },
    {
      rule: 'function',
      validator: (value) => {
        const roleElement = document.querySelector("#forgot_role");
        const role = roleElement?.value?.trim().toLowerCase();
        const email = value.trim().toLowerCase();

        if (role === "student" || role === "faculty") {
          return email.endsWith("@phinmaed.com");
        } else if (role === "admin") {
          return true;
        }
        return false;
      },
      errorMessage: "Students and Faculty must use @phinmaed.com accounts only."
    }
  ])
  .onSuccess((event) => {
    const formData = new FormData(event.target);

    fetch('/assets/server/request-reset.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.text())
    .then(data => {
      const isError = data.toLowerCase().includes('not found') || 
                      data.toLowerCase().includes('error') || 
                      data.toLowerCase().includes('invalid') || 
                      data.toLowerCase().includes('account not found');

      if (isError) {
        showError("Account not found or invalid details. Please check and try again.", "#forgot-form");
      } else {
        document.getElementById("forgot-form").innerHTML = data;
      }
    })
    .catch((err) => {
      console.error("Forgot password fetch error:", err);
      showError("Something went wrong, please try again.", "#forgot-form");
    });
  });
});


