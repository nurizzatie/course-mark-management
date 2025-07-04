<template>
  <div>
    <h2>Manage Users</h2>
    <ul>
      <li v-for="user in users" :key="user.id">
        {{ user.name }} ({{ user.role }})
      </li>
    </ul>
  </div>
</template>

<script>
import axios from 'axios';
export default {
  data() {
    return { users: [] };
  },
  mounted() {
    axios.get('http://localhost:8080/api/admin/users')
      .then(res => this.users = res.data);
  }
}
</script>
