<template>
  <div class="login">
    <h2>Login</h2>
    <form @submit.prevent="login">
      <input v-model="matric_number" placeholder="Matric Number" required />
      <input v-model="password" type="password" placeholder="Password" required />
      <button type="submit">Login</button>
      <p v-if="error" style="color:red;">{{ error }}</p>
    </form>
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
      error: ''
    };
  },
  methods: {
    async login() {
      try {
        const res = await api.post('/login', {
          matric_number: this.matric_number,
          password: this.password
        });

        const user = res.data.user;
        localStorage.setItem('user', JSON.stringify(user));

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
        this.error = err.response?.data?.error || 'Login failed: Invalid credentials';
      }
    }
  }
};
</script>
