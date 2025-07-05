<template>
  <AppLayout :role="'Admin'" :navItems="navItems" :pageTitle="pageTitle">
    <div class="container py-4">
      <h2 class="mb-4 fw-bold">Manage Users</h2>

      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Name</th>
            <th>Matric Number</th>
            <th>Email</th>
            <th>Role</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id">
            <td>{{ user.name }}</td>
            <td>{{ user.matric_number }}</td>
            <td>{{ user.email }}</td>
            <td>
              <select v-model="user.role" class="form-select">
                <option value="student">Student</option>
                <option value="lecturer">Lecturer</option>
                <option value="advisor">Advisor</option>
                <option value="admin">Admin</option>
              </select>
            </td>
            <td>
              <button class="btn btn-sm btn-primary" @click="updateRole(user)">Update</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>

<script>
import axios from 'axios'
import AppLayout from '@/layouts/AppLayout.vue'
import adminNavItems from '@/constants/adminNavItems'

export default {
  name: 'ManageUsers',
  components: { AppLayout },
  data() {
    return {
      users: [],
      navItems: adminNavItems,
      pageTitle: 'Manage Users'
    }
  },
  mounted() {
    this.fetchUsers()
  },
  methods: {
    async fetchUsers() {
      try {
        const res = await axios.get('http://localhost:8080/api/admin/users')
        this.users = res.data
      } catch (err) {
        console.error('Error fetching users:', err)
      }
    },
    async updateRole(user) {
      try {
        await axios.put(`http://localhost:8080/api/admin/users/${user.id}/role`, {
          role: user.role
        })
        alert(`Role for ${user.name} updated to ${user.role}`)
      } catch (err) {
        console.error('Update failed:', err)
        alert('Failed to update role.')
      }
    }
  }
}
</script>

<style scoped>
.table th, .table td {
  vertical-align: middle;
}
</style>