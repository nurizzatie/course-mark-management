<template>
  <div class="dashboard">
    <h2>Welcome, {{ user?.name }}</h2>
    <p>Matric Number: {{ user?.matric_number }}</p>
    <button @click="logout">Logout</button>
  </div>
</template>

<script>
export default {
  name: 'UserDashboard',
  data() {
    return {
      user: null
    };
  },
  mounted() {
    this.user = JSON.parse(localStorage.getItem('user'));
  },
  methods: {
    logout() {
      localStorage.removeItem('user');
      this.$router.push('/login');
    }
  }
}
</script>
