<template>
   <div class="login-page">
    <div class="login-box">
    <img :src="require('@/assets/utm-logo.png')" alt="UTM Logo" class="logo" />

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
      <button type="submit" :disabled="loading">
        {{ loading ? 'Logging in...' : 'Login' }}
      </button>
      <p v-if="error" class="error">{{ error }}</p>
    </form>
  </div>
  </div>
</template>

<script>
import api from '@/api';

export default {
  name: 'UserLogin',
  data() {
    return {
      matric_number: '',
      password: '',
      error: '',
      loading: false
    };
  },
  methods: {
    async login() {
      // Clear error and any old session
      this.error = '';
      this.loading = true;
      localStorage.removeItem('user');
      localStorage.removeItem('token');

      // Optional input validation
      if (!this.matric_number.trim() || !this.password.trim()) {
        this.error = 'Please fill in all fields';
        this.loading = false;
        return;
      }

      try {
        const res = await api.post('/login', {
          matric_number: this.matric_number,
          password: this.password
        });

        const user = res.data.user;
        const token = res.data.token; // optional JWT

        localStorage.setItem('user', JSON.stringify(user));
        if (token) localStorage.setItem('token', token);

        // Redirect based on role
        switch (user.role) {
          case 'student':
            this.$router.push('/dashboard/student');
            break;
          case 'lecturer':
            this.$router.push('/dashboard/lecturer');
            break;
          case 'advisor':
            this.$router.push('/dashboard/advisor');
            break;
          case 'admin':
            this.$router.push('/dashboard/admin');
            break;
          default:
            this.error = 'Unknown role';
        }
      } catch (err) {
        this.error =
          err.response?.data?.error || 'Login failed: Invalid credentials';
      } finally {
        this.loading = false;
      }
    }
  }
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
  background: url('@/assets/utm-bg-login.png') no-repeat center center;
  background-size: cover;
  overflow: hidden;
}

/* Optional white overlay for better contrast */
.login-page::before {
  content: '';
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.3); /* 50% black */
  backdrop-filter: blur(1px);
  z-index: 0;
}

/* Login box styling */
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

/* Fade-in animation */
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

/* Logo styling */
.logo {
  max-width: 380px;
  margin: 0 auto 16px;
  display: block;
}

/* Input fields */
input {
  width: 100%;
  padding: 12px;
  margin-bottom: 14px;
  border: 1px solid #ccc;
  border-radius: 5px;
  box-sizing: border-box;
  font-size: 1rem;
}

/* Button */
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

/* Error message */
.error {
  color: red;
  margin-top: 12px;
  font-size: 0.9em;
}

/* Mobile responsiveness */
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
</style>

