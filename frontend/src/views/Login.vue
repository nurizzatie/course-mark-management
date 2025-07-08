<template>
  <div class="login-page">
    <div class="login-box">
      <img
        :src="require('@/assets/app-logo.png')"
        alt="App Logo"
        class="logo"
      />

      <form @submit.prevent="login">
        <input
          v-model="matric_number"
          placeholder="Matric Number"
          autocomplete="username"
          autofocus
          required
        />
        <input
          v-model="password"
          type="password"
          placeholder="Password"
          autocomplete="current-password"
          required
        />
        <button class="mt-3" type="submit" :disabled="loading">
          {{ loading ? "Logging in..." : "Login" }}
        </button>

        <!-- 🔗 Forgot Password Link -->
        <p class="mt-2">
          <a
            @click.prevent="requestReset"
            style="cursor: pointer; color: #5d001d; text-decoration: underline"
          >
            Forgot password?
          </a>
        </p>


        <!-- 🔒 Password Reset Modal -->
        <div
          v-if="showResetModal"
          class="modal-backdrop"
          @click.self="showResetModal = false"
        >
          <div class="card text-center reset-modal">
            <div class="card-header h5 text-white bg-primary">
              Password Reset
            </div>
            <div class="card-body px-5">
              <p class="card-text py-2 ">
                Enter your email and matric number to request a password reset.
              </p>

              <input
                type="email"
                v-model="resetForm.email"
                class="form-control my-2"
                placeholder="Email"
              />
              <input
                type="text"
                v-model="resetForm.matric_number"
                class="form-control my-2"
                placeholder="Matric Number"
              />

              <button @click="submitResetRequest" class="btn btn-primary w-100">
                Reset Password
              </button>

              <div class="d-flex justify-content-center mt-3">
                <a href="#" @click.prevent="showResetModal = false"
                  >Back to Login</a
                >
              </div>
            </div>
          </div>
        </div>

        <p v-if="error" class="error">{{ error }}</p>
      </form>
    </div>
  </div>
</template>

<script>
import api from "@/api";

export default {
  name: "UserLogin",
  data() {
    return {
      matric_number: "",
      password: "",
      error: "",
      loading: false,

      // 🔥 Modal-related states
      showResetModal: false,
      resetForm: {
        email: "",
        matric_number: "",
      },
    };
  },
  methods: {
    async login() {
      this.error = "";
      this.loading = true;
      localStorage.removeItem("user");
      localStorage.removeItem("token");

      if (!this.matric_number.trim() || !this.password.trim()) {
        this.error = "Please fill in all fields";
        this.loading = false;
        return;
      }

      try {
        const res = await api.post("/login", {
          matric_number: this.matric_number,
          password: this.password,
        });

        const user = res.data.user;
        const token = res.data.token || null;
        if (token) localStorage.setItem("token", token);

        localStorage.setItem("user", JSON.stringify(user));
        localStorage.setItem("studentId", user.id);

        const role = user.role?.toLowerCase();
        if (["student", "lecturer", "advisor", "admin"].includes(role)) {
          this.$router.push(`/${role}/dashboard`);
        } else {
          this.error = `Unknown role: ${user.role}`;
        }
      } catch (err) {
        this.error =
          err.response?.data?.error || "Login failed: Invalid credentials";
      } finally {
        this.loading = false;
      }
    },

    // ✅ This triggers the modal (not prompt)
    requestReset() {
      this.resetForm.email = "";
      this.resetForm.matric_number = "";
      this.showResetModal = true;
    },

    // ✅ This handles the form inside modal
    async submitResetRequest() {
      const { email, matric_number } = this.resetForm;

      if (!email || !matric_number) {
        alert("Please fill in both email and matric number.");
        return;
      }

      try {
        const res = await api.post("/reset-request", { email, matric_number });
        alert(res.data.message || "Reset request submitted successfully.");
        this.showResetModal = false;
      } catch (err) {
        const msg = err.response?.data?.error || "Failed to request reset.";
        alert(msg);
      }
    },
  },
};
</script>

<style scoped>
.login-page {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100vh;
  padding: 20px;
  background: url("@/assets/app-background.jpg") no-repeat center center;
  background-size: cover;
  overflow: hidden;
}

.login-page::before {
  content: "";
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.3);
  backdrop-filter: blur(1px);
  z-index: 0;
}

.login-box {
  position: relative;
  z-index: 1;
  background: #ffffff;
  border-radius: 10px;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
  padding: 40px 50px;
  max-width: 500px;
  width: 100%;
  text-align: center;
  animation: fadeIn 0.6s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.logo {
  max-width: 200px;
  margin: 0 auto 16px;
  display: block;
}

input {
  width: 100%;
  padding: 12px;
  margin-bottom: 14px;
  border: 1px solid #ccc;
  border-radius: 5px;
  box-sizing: border-box;
  font-size: 1rem;
}

button {
  width: 100%;
  padding: 12px;
  background-color: #5d001d;
  color: #fff;
  border: none;
  border-radius: 5px;
  font-weight: bold;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

button:hover {
  background-color: #450016;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error {
  color: red;
  margin-top: 12px;
  font-size: 0.9em;
}

@media (max-width: 480px) {
  .login-box {
    padding: 24px;
  }

  .logo {
    max-width: 180px;
    margin-bottom: 12px;
  }

  input,
  button {
    padding: 10px;
    font-size: 0.95rem;
  }
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.4);
  backdrop-filter: blur(2px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999;
}

.reset-modal {
  width: 350px;
  animation: fadeIn 0.3s ease-in-out;
}
</style>
